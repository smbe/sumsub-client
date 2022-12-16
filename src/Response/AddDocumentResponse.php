<?php

namespace alexeevdv\SumSub\Response;

final class AddDocumentResponse
{
    /**
     * @var array
     */
    private $documentData;

    /**
     * @var string|null
     */
    private $documentId;

    public function __construct(array $documentData, string $documentId = null)
    {
        $this->documentData = $documentData;
        $this->documentId = $documentId;
    }

    public function asArray(): array
    {
        return $this->documentData;
    }

    public function documentId()
    {
        return $this->documentId;
    }
}
