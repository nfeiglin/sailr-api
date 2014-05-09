@extends('layout.simplpe')

@section('content')

<div class="form-signin large">
	<h1>Create a listing</h1>

	<form action="#" method="post">
		<input class="form-control" name="title" placeholder="Item title...">
		<input class="form-control" name="photo" placeholder="">
		<input class= "form-control" name="description" placeholder="Product Description">
		<input class= "form-control" name= "price" placeholder= "0.00">
		<label for="shipping-price" class="form-control">Shipping price></label> <input class= "form-control" name= "shipping" placehodler= "0.00">

		<select>
		  <option value="australia">Australia</option>
		  <option value="united states">United States</option>
		  <option value="new zealand">New Zealand</option>
		  <option value="england">England</option>
		  <option value="united kingdom">United Kingdom</option>
		  <option value="israel">Israel</option>
		  <option value="canada">Canada</option>
		</select>

	</form>
</div>

@stop
