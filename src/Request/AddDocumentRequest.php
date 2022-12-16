<?php

namespace alexeevdv\SumSub\Request;

use Psr\Http\Message\StreamInterface;

final class AddDocumentRequest
{
    /**
     * @var bool
     */
    private $isReturnWarnings;

    /**
     * @var string
     */
    private $applicantId;

    /**
     * @var string
     */
    private $idDocType;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string|null
     */
    private $idDocSubType;

    /**
     * @var string|null
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $middleName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $issuedDate;

    /**
     * @var string|null
     */
    private $validUntil;

    /**
     * @var string|null
     */
    private $number;

    /**
     * @var string|null
     */
    private $dob;

    /**
     * @var string|null
     */
    private $placeOfBirth;

    /**
     * @var StreamInterface|string
     */
    private $document;

    /**
     * @param string|null $applicantId
     * @param string|null $externalUserId
     */
    public function __construct(
        $document,
        $applicantId,
        $idDocType,
        $country,
        $isReturnWarnings = false,
        $idDocSubType = null,
        $firstName = null,
        $middleName = null,
        $lastName = null,
        $issuedDate = null,
        $validUntil = null,
        $number = null,
        $dob = null,
        $placeOfBirth = null
    ) {
        $this->document = $document;
        $this->applicantId = $applicantId;
        $this->idDocType = $idDocType;
        $this->country = $country;
        $this->idDocSubType = $idDocSubType;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->issuedDate = $issuedDate;
        $this->validUntil = $validUntil;
        $this->number = $number;
        $this->dob = $dob;
        $this->placeOfBirth = $placeOfBirth;
        $this->isReturnWarnings = $isReturnWarnings;
    }

    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return bool
     */
    public function isReturnWarnings()
    {
        return $this->isReturnWarnings;
    }

    /**
     * @return string|null
     */
    public function getApplicantId()
    {
        return $this->applicantId;
    }

    /**
     * @return string|null
     */
    public function getIdDocType()
    {
        return $this->idDocType;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getIdDocSubType()
    {
        return $this->idDocSubType;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * @return string|null
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string|null
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @return string|null
     */
    public function getPlaceOfBirth()
    {
        return $this->placeOfBirth;
    }

    /**
     * @return array
     */
    public function getMetadataAsArray()
    {
        $result = [];
        if (null !== $this->applicantId) {
            $result['applicantId'] = $this->applicantId;
        }
        if (null !== $this->idDocType) {
            $result['idDocType'] = $this->idDocType;
        }
        if (null !== $this->country) {
            $result['country'] = $this->country;
        }
        if (null !== $this->idDocSubType) {
            $result['idDocSubType'] = $this->idDocSubType;
        }
        if (null !== $this->firstName) {
            $result['firstName'] = $this->firstName;
        }
        if (null !== $this->middleName) {
            $result['middleName'] = $this->middleName;
        }
        if (null !== $this->lastName) {
            $result['lastName'] = $this->lastName;
        }
        if (null !== $this->issuedDate) {
            $result['issuedDate'] = $this->issuedDate;
        }
        if (null !== $this->validUntil) {
            $result['validUntil'] = $this->validUntil;
        }
        if (null !== $this->number) {
            $result['number'] = $this->number;
        }
        if (null !== $this->dob) {
            $result['dob'] = $this->dob;
        }
        if (null !== $this->placeOfBirth) {
            $result['placeOfBirth'] = $this->placeOfBirth;
        }
        return $result;
    }
}
