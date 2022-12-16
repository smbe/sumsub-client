<?php

namespace alexeevdv\SumSub\Request;

final class ApplicantInfoSetRequest
{
    /**
     * @var string
     */
    private $applicantId;

    /**
     * @var string|null
     */
    private $externalUserId = null;

    /**
     * @var string|null
     */
    private $email = null;

    /**
     * @var string|null
     */
    private $phone = null;

    /**
     * @var string|null
     */
    private $sourceKey = null;

    /**
     * @var string|null
     */
    private $lang = null;

    /**
     * @var array|null
     */
    private $questionnaires = null;

    /**
     * @var array|null
     */
    private $metadata = null;

    /**
     * @var bool|null
     */
    private $deleted = null;

    /**
     * @param string $applicantId
     */
    public function __construct(
        $applicantId,
        $externalUserId = null,
        $email = null,
        $phone = null,
        $sourceKey = null,
        $lang = null,
        $questionnaires = null,
        $metadata = null,
        $deleted = null
    ) {
        if ($applicantId === null) {
            throw new \InvalidArgumentException('Applicant ID can not be null.');
        }
        $this->applicantId = $applicantId;
        $this->externalUserId = $externalUserId;
        $this->email = $email;
        $this->phone = $phone;
        $this->sourceKey = $sourceKey;
        $this->lang = $lang;
        $this->questionnaires = $questionnaires;
        $this->metadata = $metadata;
        $this->deleted = $deleted;
    }


    public function setExternalUserId($externalUserId)
    {
        $this->externalUserId = $externalUserId;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setSourceKey($sourceKey)
    {
        $this->sourceKey = $sourceKey;
        return $this;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    public function setQuestionnaires($questionnaires)
    {
        $this->questionnaires = $questionnaires;
        return $this;
    }

    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return string
     */
    public function asArray()
    {
        $body['id'] = $this->applicantId;

        if (null !== $this->externalUserId) {
            $body['externalUserId'] = $this->externalUserId;
        }
        if (null !== $this->email) {
            $body['email'] = $this->email;
        }
        if (null !== $this->phone) {
            $body['phone'] = $this->phone;
        }
        if (null !== $this->sourceKey) {
            $body['sourceKey'] = $this->sourceKey;
        }
        if (null !== $this->lang) {
            $body['lang'] = $this->lang;
        }
        if (null !== $this->questionnaires && is_array($this->questionnaires)) {
            $body['questionnaires'] = $this->questionnaires;
        }
        if (null !== $this->metadata && is_array($this->metadata)) {
            $body['metadata'] = $this->metadata;
        }
        if (null !== $this->deleted && is_bool($this->deleted)) {
            $body['deleted'] = $this->deleted;
        }

        return $body;
    }
}
