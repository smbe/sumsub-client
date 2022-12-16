<?php

namespace alexeevdv\SumSub\Response;

final class ApplicantLevelSetResponse
{
    /**
     * @var array
     */
    private $requiredDocsData;

    public function __construct(array $requiredDocsData)
    {
        $this->requiredDocsData = $requiredDocsData;
    }

    public function asArray(): array
    {
        return $this->requiredDocsData;
    }
}
