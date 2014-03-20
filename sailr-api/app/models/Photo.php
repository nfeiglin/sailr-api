<?php

class Photo extends \Eloquent
{
    protected $fillable = [];
    protected $softDeletes = true;

    public function item()
    {
        return $this->belongsTo('Item');
    }

    public static function generateUniquePath()
    {
        $isUnique = false;
        do {
            $unique1 = uniqid('10510997103101', true);
            $unique2 = uniqid('5841', true);
            $directory = $unique1 . '/' . $unique2;

            if (!is_dir($directory)) {
                $isUnique = true;
                mkdir('path/to/directory');
            }
        } while (!$isUnique);

        return $directory . '.jpg';
    }
}