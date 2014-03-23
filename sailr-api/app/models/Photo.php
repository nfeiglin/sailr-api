<?php

class Photo extends \Eloquent
{
    protected $fillable = [];
    protected $softDelete = true;

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
            $directory = '/' . $unique2;
            $testdir = public_path() . '/' . $unique2;
            // print "DIR is: ";
            //  print $directory;
            if (!is_dir($testdir)) {
                $isUnique = true;
                mkdir($testdir);
            }
        } while (!$isUnique);

        return $testdir . '/' . $unique1 . '.jpg';
    }
}