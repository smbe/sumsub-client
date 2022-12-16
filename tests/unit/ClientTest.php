<?php

namespace tests\unit;

use alexeevdv\SumSub\Client;
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
use alexeevdv\SumSub\Request\RequestSignerInterface;
use alexeevdv\SumSub\Request\ResetApplicantRequest;
use alexeevdv\SumSub\Request\ShareTokenRequest;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class ClientTest extends Unit
{
    public function testGetAccessTokenWithoutTtlInSeconds(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('https', $request->getUri()->getScheme());
                self::assertSame('api.sumsub.com', $request->getUri()->getHost());
                self::assertSame('/resources/accessTokens', $request->getUri()->getPath());
                self::assertSame('userId=123456&levelName=test-level', $request->getUri()->getQuery());

                return new Response(200, [], json_encode([
                    'token' => '654321',
                    'userId' => '123456',
                ]));
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $accessTokenResponse = $client->getAccessToken(
            new AccessTokenRequest('123456', 'test-level')
        );

        self::assertSame('654321', $accessTokenResponse->getToken());
        self::assertSame('123456', $accessTokenResponse->getUserId());
    }

    public function testGetAccessTokenWithTtlInSeconds(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/accessTokens', $request->getUri()->getPath());
                self::assertSame('userId=123456&levelName=test-level&ttlInSecs=3600', $request->getUri()->getQuery());

                return new Response(200, [], json_encode([
                    'token' => '654321',
                    'userId' => '123456',
                ]));
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $accessTokenResponse = $client->getAccessToken(
            new AccessTokenRequest('123456', 'test-level', 3600)
        );

        self::assertSame('654321', $accessTokenResponse->getToken());
        self::assertSame('123456', $accessTokenResponse->getUserId());
    }

    public function testGetAccessTokenWhenRequestFailed(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                throw new class () extends \Exception implements ClientExceptionInterface {
                };
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(TransportException::class);
        $client->getAccessToken(new AccessTokenRequest('123456', 'test-level'));
    }

    public function testGetAccessTokenWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                return new Response(500, [], 'Smth went wrong');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->getAccessToken(new AccessTokenRequest('123456', 'test-level'));
    }

    public function testGetApplicantDataByApplicantId(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/one', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(200, [], json_encode(['a' => 'b']));
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $applicantDataResponse = $client->getApplicantData(new ApplicantDataRequest('123456'));
        self::assertSame(['a' => 'b'], $applicantDataResponse->asArray());
    }

    public function testGetApplicantDataByExternalUserId(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/-;externalUserId=654321/one', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(200, [], json_encode(['a' => 'b']));
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $applicantDataResponse = $client->getApplicantData(new ApplicantDataRequest(null, '654321'));
        self::assertSame(['a' => 'b'], $applicantDataResponse->asArray());
    }

    public function testGetApplicantDataWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/one', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(500, [], 'Something went wrong');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->getApplicantData(new ApplicantDataRequest('123456'));
    }

    public function testGetApplicantDataWhenCanNotDecodeResponse(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                return new Response(200, [], 'Not a JSON string');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
         $client->getApplicantData(new ApplicantDataRequest('123456'));
    }

    public function testResetApplicant(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/reset', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(200, [], json_encode(['ok' => 1]));
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $client->resetApplicant(new ResetApplicantRequest('123456'));
    }

    public function testResetApplicantWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/reset', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(500, [], 'Something went wrong');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->resetApplicant(new ResetApplicantRequest('123456'));
    }

    public function testResetApplicantWhenCanNotDecodeResponse(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                return new Response(200, [], 'Not a JSON string');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->resetApplicant(new ResetApplicantRequest('123456'));
    }

    public function testGetApplicantStatus(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/requiredIdDocsStatus', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(200, [], json_encode(['a' => 'b']));
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $applicantStatusResponse = $client->getApplicantStatus(new ApplicantStatusRequest('123456'));
        self::assertSame(['a' => 'b'], $applicantStatusResponse->asArray());
    }

    public function testGetApplicantStatusWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/requiredIdDocsStatus', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(500, [], 'Something went wrong');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->getApplicantStatus(new ApplicantStatusRequest('123456'));
    }

    public function testGetApplicantStatusWhenCanNotDecodeResponse(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                return new Response(200, [], 'Not a JSON string');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->getApplicantStatus(new ApplicantStatusRequest('123456'));
    }


    public function testGetApplicantStatusSdk(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/status', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(200, [], json_encode(['a' => 'b']));
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $applicantStatusResponse = $client->getApplicantStatusSdk(new ApplicantStatusSdkRequest('123456'));
        self::assertSame(['a' => 'b'], $applicantStatusResponse->asArray());
    }

    public function testGetApplicantStatusSdkWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123456/status', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(500, [], 'Something went wrong');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->getApplicantStatusSdk(new ApplicantStatusSdkRequest('123456'));
    }

    public function testGetApplicantStatusSdkWhenCanNotDecodeResponse(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                return new Response(200, [], 'Not a JSON string');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->getApplicantStatusSdk(new ApplicantStatusSdkRequest('123456'));
    }

    public function testGetShareToken(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/accessTokens/-/shareToken', $request->getUri()->getPath());
                self::assertSame('applicantId=123456&forClientId=abc123&ttlInSecs=1000', $request->getUri()->getQuery());

                return new Response(200, [], json_encode([
                    'token' => '555666',
                    'forClientId' => 'abc123',
                ]));
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $applicantTokenResponse = $client->getShareToken(
            new ShareTokenRequest('123456', 'abc123', 1000)
        );
        self::assertSame('555666', $applicantTokenResponse->getToken());
        self::assertSame('abc123', $applicantTokenResponse->getClientId());
    }

    public function testGetShareTokenWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/accessTokens/-/shareToken', $request->getUri()->getPath());
                self::assertSame('applicantId=123456&forClientId=abc123&ttlInSecs=1000', $request->getUri()->getQuery());

                return new Response(500, [], json_encode([
                    'token' => '555666',
                    'forClientId' => 'abc123',
                ]));
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $this->expectException(BadResponseException::class);
        $client->getShareToken(new ShareTokenRequest('123456', 'abc123', 1000));
    }

    public function testGetShareTokenWhenCanNotDecodeResponse(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/accessTokens/-/shareToken', $request->getUri()->getPath());
                self::assertSame('applicantId=123456&forClientId=abc123&ttlInSecs=1000', $request->getUri()->getQuery());

                return new Response(500, [], 'Not a JSON string');
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $this->expectException(BadResponseException::class);
        $client->getShareToken(new ShareTokenRequest('123456', 'abc123', 1000));
    }

    public function testImportApplicant(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/-/import', $request->getUri()->getPath());
                self::assertSame(
                    'shareToken=123456&resetIdDocSetTypes=SELFIE%2CIDENTITY&trustReview=true&userId=321&levelName=kyclvl', 
                    $request->getUri()->getQuery()
                );

                return new Response(200, [], json_encode(['id' => '555']));
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $applicantDataResponse = $client->importApplicant(
            new ImportApplicantRequest('123456', ['SELFIE','IDENTITY'], true, '321', 'kyclvl')
        );
        self::assertSame(['id' => '555'], $applicantDataResponse->asArray());
    }

    public function testImportApplicantRequired(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/-/import', $request->getUri()->getPath());
                self::assertSame('shareToken=123456', $request->getUri()->getQuery());

                return new Response(200, [], json_encode(['id' => '555']));
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $applicantDataResponse = $client->importApplicant(new ImportApplicantRequest('123456'));
        self::assertSame(['id' => '555'], $applicantDataResponse->asArray());
    }

    public function testImportApplicantWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/-/import', $request->getUri()->getPath());
                self::assertSame('shareToken=123456', $request->getUri()->getQuery());

                return new Response(500, [], json_encode(['id' => '555']));
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $this->expectException(BadResponseException::class);
        $client->importApplicant(new ImportApplicantRequest('123456'));
    }

    public function testImportApplicantWhenCanNotDecodeResponse(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/-/import', $request->getUri()->getPath());
                self::assertSame('shareToken=123456', $request->getUri()->getQuery());

                return new Response(200, [], 'Not a JSON string');
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $this->expectException(BadResponseException::class);
        $client->importApplicant(new ImportApplicantRequest('123456'));
    }


    public function testGetDocumentImages(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/inspections/123456/resources/654321', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(200, ['Content-Type' => 'text/plain'], 'contents');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $applicantStatusResponse = $client->getDocumentImages(new DocumentImagesRequest('123456', '654321'));

        self::assertSame('contents', (string) $applicantStatusResponse->asStream());
        self::assertSame('text/plain', $applicantStatusResponse->getContentType());
    }

    public function testGetDocumentImagesWhenResponseCodeIsNot200(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/inspections/123456/resources/654321', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());

                return new Response(500, [], 'Something went wrong');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $this->expectException(BadResponseException::class);
        $client->getDocumentImages(new DocumentImagesRequest('123456', '654321'));
    }



    public function testAddDocument(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123123/info/idDoc', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());
                
                return new Response(200, [
                    'Content-Type' => 'text/plain',
                    'X-Image-Id' => '4321',
                ], '{"idDocType": "DOCTYPE", "country": "CNT"}');
            }),
        ]);

        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $addDocumentResult = $client->AddDocument(new AddDocumentRequest(
            'document',
            '123123',
            'DOCTYPE',
            'CNT', 
            false
        ), $this->getStreamFactory());

        self::assertSame(["idDocType" => "DOCTYPE",  "country" => "CNT"], $addDocumentResult->asArray());
        self::assertSame('4321', $addDocumentResult->documentId());
    }

    public function testSetApplicantLevel(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/123/moveToLevel', $request->getUri()->getPath());
                self::assertSame('name=level123', $request->getUri()->getQuery());

                return new Response(200, [], json_encode(['arr' => 'res']));
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());
        $applicantDataResponse = $client->setApplicantLevel(new ApplicantLevelSetRequest('123','level123'));
        self::assertSame(['arr' => 'res'], $applicantDataResponse->asArray());
    }


    public function testSetApplicantInfo(): void
    {
        /** @var ClientInterface $httpClient */
        $httpClient = $this->makeEmpty(ClientInterface::class, [
            'sendRequest' => Expected::once(static function (RequestInterface $request): ResponseInterface {
                self::assertSame('/resources/applicants/', $request->getUri()->getPath());
                self::assertSame('', $request->getUri()->getQuery());
                self::assertSame(
                    '{"id":"123456","phone":"111222333","questionnaires":[{"id":"1"},{"id":"2"}],"deleted":true}', 
                    (string) $request->getBody()
                );

                return new Response(200, [], json_encode(['a' => 'b']));
            }),
        ]);
        $client = new Client($httpClient, $this->getRequestFactory(), $this->getRequestSigner());

        $request = new ApplicantInfoSetRequest('123456');
        $request->setPhone('111222333');
        $request->setQuestionnaires([
            0 => ['id' => '1'],
            1 => ['id' => '2']
        ]);
        $request->setDeleted(true);

        $applicantInfoResponse = $client->setApplicantInfo($request, $this->getStreamFactory());
        self::assertSame(['a' => 'b'], $applicantInfoResponse->asArray());
    }

    private function getRequestSigner(): RequestSignerInterface
    {
        /** @var RequestSignerInterface $signer */
        $signer = $this->makeEmpty(RequestSignerInterface::class, [
            'sign' => static function (RequestInterface $request): RequestInterface {
                return $request;
            },
        ]);
        return $signer;
    }

    private function getRequestFactory(): RequestFactoryInterface
    {
        /** @var RequestFactoryInterface $factory */
        $factory = $this->makeEmpty(RequestFactoryInterface::class, [
            'createRequest' => static function (string $method, $uri): RequestInterface {
                return new Request($method, $uri);
            },
        ]);
        return $factory;
    }

    private function getStreamFactory(): StreamFactoryInterface
    {
        /** @var StreamFactoryInterface $factory */
        
        $factory = $this->makeEmpty(StreamFactoryInterface::class, [
            'createStream' => static function (string $content): StreamInterface {
                return Utils::streamFor($content);
            },
        ]);
        return $factory;
    }

}
