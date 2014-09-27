<?php

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();


        /*
         * Truncate deletes all the data in the DB. You may want to comment these out!

        
        Item::truncate();
        User::truncate();
        Relationship::truncate();
        


        */

        //Collection::truncate();

        //$this->call('UsersTableSeeder');
        //$this->call('RelationshipsTableSeeder');
        $this->call('ItemsTableSeeder');
       // $this->call('ProfileImgsTableSeeder');
        //$this->call('CollectionsTableSeeder');
    }

}