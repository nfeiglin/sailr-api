<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 21/09/2014
 * Time: 6:49 PM
 */

namespace Sailr\Paginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Contracts\ArrayableInterface;
use ArrayAccess;
use Illuminate\Support\Contracts\JsonableInterface;
use JsonSerializable;
use Illuminate\Routing\UrlGenerator;

class SailrPaginator implements ArrayableInterface, JsonableInterface, ArrayAccess, JsonSerializable {

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var string
     */
    protected $pageName = "page";

    /**
     * @var mixed $items The paginated objects / items
     */
    protected $items;

    public function __construct($items, Paginator $paginator, UrlGenerator $urlGenerator)
    {
        $this->items = $items;
        $this->paginator = $paginator;
        $this->urlGenerator = $urlGenerator;
    }

    public function getCollection() {
        return $this->items;
    }

    public function getUnderlyingData() {
        return $this->getCollection();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'per_page' => $this->paginator->getPerPage(),
            'current_page' => (int) $this->paginator->getCurrentPage(),
            'last_page' => (int) $this->paginator->getLastPage(),
            'links' => [
                'next_page' => $this->getNextPageUrl(),
                'prev_page' => $this->getPreviousPageUrl()
            ]
        ];
    }

    public function getNextPageUrl() {
        if ((int) $this->paginator->getCurrentPage() !== (int) $this->paginator->getLastPage()) {
            return $this->urlGenerator->current() . '?' . http_build_query([$this->pageName => $this->paginator->getCurrentPage() + 1]);
        }

        return null;
    }

    public function getPreviousPageUrl() {
        if ((int)$this->paginator->getCurrentPage() >= 2) {
            return $this->urlGenerator->current() . '?' .  http_build_query([$this->pageName => $this->paginator->getCurrentPage() - 1]);
        }

        return null;
    }


    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {

        if (property_exists($this, $offset)) {
            return $this->$offset;
        }
        else {
            return false;
        }
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return $this->toJson();
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetUnset($offset) {

    }
}