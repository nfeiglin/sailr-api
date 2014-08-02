<?php

class CollectionsController extends \BaseController
{

    /**
     * Display a listing of the resource.
     * GET /collections
     * @param string $username
     * @return Response
     */
    public function index($username)
    {
        return View::make('collections.index')->with('username', $username);
    }



    /**
     * Show the form for creating a new resource.
     * GET /collections/create
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * POST /collections
     *
     * @return Response
     */
    public function store()
    {

    }

    /**
     * Display the specified resource.
     * GET /collections/{id}
     *
     * @param string $username
     * @param  int $id
     * @return Response
     */
    public function show($username, $id)
    {
        return View::make('collections.show', compact('username', 'id'));
    }


    /**
     * Show the form for editing the specified resource.
     * GET /collections/{id}/edit
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * PUT /collections/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /collections/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}