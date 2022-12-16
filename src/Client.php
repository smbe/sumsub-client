<?php

namespace alexeevdv\SumSub;

use alexeevdv\SumSub\Exception\BadResponseException;
use alexeevdv\SumSub\Exception\TransportException;
use alexeevdv\SumSub\Request\AccessTokenRequest;
use alexeevdv\SumSub\Request\AddDocumentRequest;
use alexeevdv\SumSub\Request\ApplicantDataRequest;
use alexeevdv\SumSub\Request\ApplicantInfoSetRequest;
use alexeevdv\SumSub\Request\ApplicantLevelSetRequest;
use alexeevdv\SumSub\Request\ApplicantStatusRequest;
use alexeevdv\SumSub\Request\ApplicantStatusSdkRequest;
use alexeevdv\SumSub\Request\DocumentImagesRequest;
use alexeevdv\SumSub\Request\ImportApplicantRequest;
use alexeevdv\SumSub\Request\InspectionChecksRequest;
use alexeevdv\SumSub\Request\RequestSignerInterface;
use alexeevdv\SumSub\Request\ResetApplicantRequest;
use alexeevdv\SumSub\Request\ShareTokenRequest;
use alexeevdv\SumSub\Response\AccessTokenResponse;
use alexeevdv\SumSub\Response\AddDocumentResponse;
use alexeevdv\SumSub\Response\ApplicantDataResponse;
use alexeevdv\SumSub\Response\ApplicantInfoSetResponse;
use alexeevdv\SumSub\Response\ApplicantLevelSetResponse;
use alexeevdv\SumSub\Response\ApplicantStatusResponse;
use alexeevdv\SumSub\Response\ApplicantStatusSdkResponse;
use alexeevdv\SumSub\Response\DocumentImagesResponse;
use alexeevdv\SumSub\Response\InspectionChecksResponse;
use alexeevdv\SumSub\Response\ResetApplicantResponse;
use alexeevdv\SumSub\Response\ShareTokenResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class Client implements ClientInterface
{
    public const PRODUCTION_BASE_URI = 'https://api.sumsub.com';
    public const STAGING_BASE_URI = 'https://test-api.sumsub.com';

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var RequestSignerInterface
     */
    private $requestSigner;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param HttpClientInterface     $httpClient
     * @param RequestFactoryInterface $requestFactory
     * @param RequestSignerInterface  $requestSigner
     * @param string                  $baseUrl
     */
    public function __construct(
        $httpClient,
        $requestFactory,
        $requestSigner,
        $baseUrl = self::PRODUCTION_BASE_URI
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->requestSigner = $requestSigner;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getAccessToken(AccessTokenRequest $request): AccessTokenResponse
    {
        $queryParams = [
            'userId' => $request->getUserId(),
            'levelName' => $request->getLevelName(),
        ];

        if ($request->getTtlInSecs() !== null) {
            $queryParams['ttlInSecs'] = $request->getTtlInSecs();
        }

        $url = sprintf('%s/resources/accessTokens?%s', $this->baseUrl, http_build_query($queryParams));

        $httpRequest = $this->createApiRequest('POST', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        $decodedResponse = $this->decodeResponse($httpResponse);

        return new AccessTokenResponse($decodedResponse['token'], $decodedResponse['userId']);
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getApplicantData(ApplicantDataRequest $request): ApplicantDataResponse
    {
        if ($request->getApplicantId() !== null) {
            $url = $this->baseUrl . '/resources/applicants/' . $request->getApplicantId() . '/one';
        } else {
            $url = $this->baseUrl . '/resources/applicants/-;externalUserId=' . $request->getExternalUserId() . '/one';
        }

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantDataResponse($this->decodeResponse($httpResponse));
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function resetApplicant(ResetApplicantRequest $request): void
    {
        $url = $this->baseUrl . '/resources/applicants/' . $request->getApplicantId() . '/reset';

        $httpRequest = $this->createApiRequest('POST', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        $decodedResponse = $this->decodeResponse($httpResponse);
        $isOk = ($decodedResponse['ok'] ?? 0) === 1;

        if (!$isOk) {
            throw new BadResponseException($httpResponse);
        }
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getApplicantStatus(ApplicantStatusRequest $request): ApplicantStatusResponse
    {
        $url = $this->baseUrl . '/resources/applicants/' . $request->getApplicantId() . '/requiredIdDocsStatus';

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantStatusResponse($this->decodeResponse($httpResponse));
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getApplicantStatusSdk(ApplicantStatusSdkRequest $request): ApplicantStatusSdkResponse
    {
        $url = sprintf(
            '%s/resources/applicants/%s/status',
            $this->baseUrl,
            $request->getApplicantId()
        );

        $httpRequest = $this->createApiRequest('GET', $url);

        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantStatusSdkResponse($this->decodeResponse($httpResponse));
    }


    public function getShareToken(ShareTokenRequest $request): ShareTokenResponse
    {
        $queryParams = [
            'applicantId' => $request->getApplicantId(),
            'forClientId' => $request->getClientId()
        ];

        if ($request->getTtlInSecs() !== null) {
            $queryParams['ttlInSecs'] = $request->getTtlInSecs();
        }

        $url = sprintf(
            '%s/resources/accessTokens/-/shareToken?%s',
            $this->baseUrl,
            http_build_query($queryParams)
        );

        $httpRequest = $this->createApiRequest('POST', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        $decodedResponse = $this->decodeResponse($httpResponse);

        return new ShareTokenResponse($decodedResponse['token'], $decodedResponse['forClientId']);
    }

    public function importApplicant(ImportApplicantRequest $request): ApplicantDataResponse
    {
        $queryParams = [
            'shareToken' => $request->getShareToken()
        ];

        if ($request->getResetIdDocSetTypes() !== null) {
            $queryParams['resetIdDocSetTypes'] = join(',', $request->getResetIdDocSetTypes());
        }

        if ($request->getTrustReview() !== null) {
            $queryParams['trustReview'] = (true == $request->getTrustReview()) ? 'true' : 'false';
        }

        if ($request->getUserId() !== null) {
            $queryParams['userId'] = $request->getUserId();
        }

        if ($request->getLevelName() !== null) {
            $queryParams['levelName'] = $request->getLevelName();
        }

        $url = sprintf(
            '%s/resources/applicants/-/import?%s',
            $this->baseUrl,
            http_build_query($queryParams)
        );

        $httpRequest = $this->createApiRequest('POST', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantDataResponse($this->decodeResponse($httpResponse));
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function getDocumentImages(DocumentImagesRequest $request): DocumentImagesResponse
    {
        $url = $this->baseUrl . '/resources/inspections/' . $request->getInspectionId() .
            '/resources/' . $request->getImageId();

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new DocumentImagesResponse($httpResponse);
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function addDocument(AddDocumentRequest $request, StreamFactoryInterface $streamFactory): AddDocumentResponse
    {
        $url = sprintf(
            '%s/resources/applicants/%s/info/idDoc',
            $this->baseUrl,
            $request->getApplicantId()
        );

        $metadata = json_encode($request->getMetadataAsArray());

        $documentData = $request->getDocument();
        if ($documentData instanceof StreamInterface) {
            $documentData = (string) $documentData;
        }

        $boundary = sha1(uniqid('', true));

        $requestBody = '--' . $boundary . "\r\n" .
            'Content-Disposition: form-data; name="metadata"' . "\r\n" .
            'Content-Length: ' . strlen($metadata) . "\r\n" .
            "\r\n" .
            $metadata . "\r\n";

        $requestBody .= '--' . $boundary . "\r\n" .
            'Content-Disposition: form-data; name="content"; filename=""' . "\r\n" .
            'Content-Length: ' . strlen($documentData) . "\r\n" .
            "\r\n" .
            $documentData . "\r\n" .
            '--' . $boundary . '--';

        $httpRequest = $this->requestFactory->createRequest('POST', $url)
            ->withHeader('Content-Type', sprintf('multipart/form-data; boundary="%s"', $boundary))
            ->withHeader('X-Return-Doc-Warnings', $request->isReturnWarnings() ? 'true' : 'false')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($requestBody));

        $httpRequest = $this->requestSigner->sign($httpRequest);

        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new AddDocumentResponse(
            $this->decodeResponse($httpResponse),
            $httpResponse->getHeader("X-Image-Id")[0]
        );
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function setApplicantLevel(ApplicantLevelSetRequest $request): ApplicantLevelSetResponse
    {
        $queryParams = [
            'name' => $request->getLevelName(),
        ];

        $url = sprintf(
            '%s/resources/applicants/%s/moveToLevel?%s',
            $this->baseUrl,
            $request->getApplicantId(),
            http_build_query($queryParams)
        );

        $httpRequest = $this->createApiRequest('POST', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantLevelSetResponse($this->decodeResponse($httpResponse));
    }


    public function getInspectionChecks(InspectionChecksRequest $request): InspectionChecksResponse
    {
        $url = $this->baseUrl . '/resources/inspections/' . $request->getInspectionId() .
            '/checks';

        $httpRequest = $this->createApiRequest('GET', $url);
        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new InspectionChecksResponse($this->decodeResponse($httpResponse));
    }

    /**
     * @throws BadResponseException
     * @throws TransportException
     */
    public function setApplicantInfo(
        ApplicantInfoSetRequest $request,
        StreamFactoryInterface $streamFactory
    ): ApplicantInfoSetResponse {
        $url = sprintf('%s/resources/applicants/', $this->baseUrl);

        $requestData = json_encode($request->asArray());

        $httpRequest = $this->requestFactory->createRequest('PATCH', $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($streamFactory->createStream($requestData));

        $httpRequest = $this->requestSigner->sign($httpRequest);

        $httpResponse = $this->sendApiRequest($httpRequest);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new BadResponseException($httpResponse);
        }

        return new ApplicantInfoSetResponse($this->decodeResponse($httpResponse));
    }

    private function createApiRequest($method, $uri): RequestInterface
    {
        $httpRequest = $this->requestFactory
            ->createRequest($method, $uri)
            ->withHeader('Accept', 'application/json');
        $httpRequest = $this->requestSigner->sign($httpRequest);

        return $httpRequest;
    }

    /**
     * @throws TransportException
     */
    private function sendApiRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new TransportException($e);
        }
    }

    /**
     * @throws BadResponseException
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        try {
            $result = json_decode($response->getBody()->getContents(), true);
            if ($result === null) {
                throw new \Exception(json_last_error_msg());
            }
            return $result;
        } catch (\Throwable $e) {
            throw new BadResponseException($response, $e);
        }
    }
}
