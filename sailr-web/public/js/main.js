
var totalPriceText = $('#total-price');
var checkoutBtn = $('#checkout-btn');
$(document).ready(function(){
	var imgGallery = $('.img-gallery');
    imgGallery.slick({
        dots: true,
        arrows: false

    });




// This example displays an address form, using the autocomplete feature
// of the Google Places API to help users fill in the information.
$('#autocomplete').show();
$('#hidden-form').hide();

var total = 0;

if (!typeof item === 'undefined') {
    item['price'] = parseFloat(item['price']);
}


var doUpdatePrice = function() {
  if($('#country').val() == item['country']) {
    //Domestic shipping
    total = item['price'] + domesticShippingPrice;
    var text = 
    totalPriceText.html(item['currency'] + total.toFixed(2));
  }

  else {
    //International shipping
    total = item['price'] + internationalShippingPrice;
    totalPriceText.html(item['currency'] + total.toFixed(2));
  }
    checkoutBtn.removeAttr('disabled');
}

$('#country').change(function() {

  doUpdatePrice();

});
var placeSearch, autocomplete;
var componentForm = {
  street_number: 'short_name',
  route: 'long_name',
  locality: 'long_name',
  administrative_area_level_1: 'short_name',
  country: 'long_name',
  postal_code: 'short_name'
};

function initialize() {
  // Create the autocomplete object, restricting the search
  // to geographical location types.
  autocomplete = new google.maps.places.Autocomplete(
      /** @type {HTMLInputElement} */(document.getElementById('autocomplete')),
      { types: ['geocode'] });
  // When the user selects an address from the dropdown,
  // populate the address fields in the form.
  google.maps.event.addListener(autocomplete, 'place_changed', function() {
    fillInAddress();
  });
}

// [START region_fillform]
function fillInAddress() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();

  for (var component in componentForm) {
    document.getElementById(component).value = '';
    document.getElementById(component).readOnly = false;
  }

  // Get each component of the address from the place details
  // and fill the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm[addressType]) {
      var val = place.address_components[i][componentForm[addressType]];
      document.getElementById(addressType).value = val;
      document.getElementById(addressType).readOnly = true;
    }
  }

  $('#hidden-form').show();
  doUpdatePrice();
}
// [END region_fillform]

// [START region_geolocation]
// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = new google.maps.LatLng(
          position.coords.latitude, position.coords.longitude);
      autocomplete.setBounds(new google.maps.LatLngBounds(geolocation,
          geolocation));
    });
  }
}
// [END region_geolocation]

initialize();
//geolocate();

});

//Code for user.show page

var unfollowButton = $('#unfollow-btn');

unfollowButton.on('mouseover', function() {
    console.log('MOUSEOVER');
    $(this).text('Unfollow');
    $(this).addClass('btn-danger');

});

unfollowButton.on('mouseleave', function() {
    unfollowButton.text('Following');
    unfollowButton.removeClass('btn-danger');
});

/*$('select').selectpicker();*/


var mediumEditor = new MediumEditor('[data-md-ed]', {
    firstHeader: 'h3',
    secondHeader:'h4',
    cleanPastedHTML: false,
    buttons: ['bold', 'italic', 'anchor', 'header1', 'header2', 'blockquote', 'unorderedlist'],
    disableEditing: false


});




