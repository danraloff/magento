<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class CustomFieldMappingDtoCollection
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto
 */
class CustomFieldMappingDtoCollection implements IteratorAggregate
{
    /** @var array */
    private $items = [];

    /**
     * @param array $data
     * @return CustomFieldMappingDtoCollection
     */
    public static function createFromRequestData(array $data)
    {
        $collection = new self();

        foreach ($data['custom'] as $key => $customs) {

            $collection->add(
                new CustomFieldMappingDto(
                    $data['custom'][$key],
                    $data['gr_custom'][$key]
                )
            );
        }

        return $collection;
    }

    /**
     * @param CustomFieldMappingDto $item
     */
    public function add(CustomFieldMappingDto $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}