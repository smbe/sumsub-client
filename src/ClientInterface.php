<?php

namespace alexeevdv\SumSub;

use alexeevdv\SumSub\Exception\Exception;
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
use alexeevdv\SumSub\Request\ResetApplicantRequest;
use alexeevdv\SumSub\Request\ShareTokenRequest;
use alexeevdv\SumSub\Response\AccessTokenResponse;
use alexeevdv\SumSub\Response\AddDocumentResponse;
use alexeevdv\SumSub\Response\ApplicantStatusSdkResponse;
use alexeevdv\SumSub\Response\ApplicantDataResponse;
use alexeevdv\SumSub\Response\ApplicantInfoSetResponse;
use alexeevdv\SumSub\Response\ApplicantLevelSetResponse;
use alexeevdv\SumSub\Response\ApplicantStatusResponse;
use alexeevdv\SumSub\Response\DocumentImagesResponse;
use alexeevdv\SumSub\Response\InspectionChecksResponse;
use alexeevdv\SumSub\Response\ShareTokenResponse;
use Psr\Http\Message\StreamFactoryInterface;

interface ClientInterface
{
    /**
     * Get access token for SDKs
     *
     * @see    https://developers.sumsub.com/api-reference/#access-tokens-for-sdks
     * @throws Exception
     */
    public function getAccessToken(AccessTokenRequest $request): AccessTokenResponse;

    /**
     * Get applicant data
     *
     * @see    https://developers.sumsub.com/api-reference/#getting-applicant-data
     * @throws Exception
     */
    public function getApplicantData(ApplicantDataRequest $request): ApplicantDataResponse;

    /**
     * Resetting an applicant
     *
     * @see    https://developers.sumsub.com/api-reference/#resetting-an-applicant
     * @throws Exception
     */
    public function resetApplicant(ResetApplicantRequest $request): void;

    /**
     * Get applicant status
     *
     * @see    https://developers.sumsub.com/api-reference/#getting-applicant-status-api
     * @throws Exception
     */
    public function getApplicantStatus(ApplicantStatusRequest $request): ApplicantStatusResponse;

    /**
     * Get applicant status SDK
     *
     * @see    https://developers.sumsub.com/api-reference/#getting-applicant-status-sdk
     * @throws Exception
     */
    public function getApplicantStatusSdk(ApplicantStatusSdkRequest $request): ApplicantStatusSdkResponse;

    /**
     * Get document images
     *
     * @see    https://developers.sumsub.com/api-reference/#getting-document-images
     * @throws Exception
     */
    public function getDocumentImages(DocumentImagesRequest $request): DocumentImagesResponse;

    /**
     * Adding an ID document
     *
     * @see https://developers.sumsub.com/api-reference/#adding-an-id-document
     * @throws Exception
     */
    public function addDocument(
        AddDocumentRequest $request,
        StreamFactoryInterface $streamFactory
    ): AddDocumentResponse;

    /**
     * Get inspection checks
     *
     * @throws Exception
     */
    public function getInspectionChecks(InspectionChecksRequest $request): InspectionChecksResponse;

    /**
     * Generating a share token
     *
     * @see    https://developers.sumsub.com/api-reference/#generating-a-share-token
     * @throws Exception
     */
    public function getShareToken(ShareTokenRequest $request): ShareTokenResponse;

    /**
     * Importing an applicant
     *
     * @see    https://developers.sumsub.com/api-reference/#importing-an-applicant
     * @throws Exception
     */
    public function importApplicant(ImportApplicantRequest $request): ApplicantDataResponse;

    /**
     * Changing required document set (level)
     *
     * @see    https://developers.sumsub.com/api-reference/#changing-required-document-set-level
     * @throws Exception
     */
    public function setApplicantLevel(ApplicantLevelSetRequest $request): ApplicantLevelSetResponse;


    /**
     * Changing top-level info
     *
     * @see https://developers.sumsub.com/api-reference/#changing-top-level-info
     * @throws Exception
     */
    public function setApplicantInfo(
        ApplicantInfoSetRequest $request,
        StreamFactoryInterface $streamFactory
    ): ApplicantInfoSetResponse;
}
