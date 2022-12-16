<?php

namespace alexeevdv\SumSub\Request;

final class ApplicantLevelSetRequest
{
    /**
     * @var string
     */
    private $applicantId;

    /**
     * @var string
     */
    private $levelName;

    /**
     * @param string $applicantId
     */
    public function __construct($applicantId, $levelName)
    {
        if ($applicantId === null) {
            throw new \InvalidArgumentException('Applicant ID can not be null.');
        }
        $this->applicantId = $applicantId;

        if ($levelName === null) {
            throw new \InvalidArgumentException('Level name can not be null.');
        }
        $this->levelName = $levelName;
    }

    /**
     * @return string
     */
    public function getApplicantId()
    {
        return $this->applicantId;
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
    }
}
