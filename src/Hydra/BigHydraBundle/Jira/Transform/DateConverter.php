<?php
namespace Hydra\BigHydraBundle\Jira\Transform;

class DateConverter
{
    /** @var string[] */
    protected $dateFieldNames;

    /**
     * @param string[] $dateFieldNames
     */
    public function __construct(array $dateFieldNames)
    {
        $this->dateFieldNames = $dateFieldNames;
    }


    /**
     * @param array $document
     */
    public function convertRecursive(array &$document)
    {
        $this->convert($document);
        foreach ($document as &$value) {
            if (is_array($value)) {
                $this->convertRecursive($value);
            }
        }
    }

    /**
     * @param array $document
     */
    public function convert(array &$document)
    {
        foreach ($this->dateFieldNames as $fieldName) {
            $this->convertDateField($fieldName, $document);
        }
    }

    /**
     * @param string $fieldName
     * @param array $document
     */
    protected function convertDateField($fieldName, array &$document)
    {
        if (array_key_exists($fieldName, $document)) {
            $document[$fieldName.'Date'] =  new \MongoDate(strtotime($document[$fieldName]));
        }
    }
}
