<?php

namespace alexeevdv\SumSub\Response;

final class ApplicantInfoSetResponse
{
    /**
     * @var array
     */
    private $applicantData;

    public function __construct(array $applicantData)
    {
        $this->applicantData = $applicantData;
    }

    public function asArray(): array
    {
        return $this->applicantData;
    }
}
