@extends('emails.layout.main')
@section('content')

<table class="body">
    <tr>
        <td class="center" align="center" valign="top">
            <center>

                <table class="row header">
                    <tr>
                        <td class="center" align="center">
                            <center>


                                        </td>
                                    </tr>
                                </table>

                            </center>
                        </td>
                    </tr>
                </table>

                <table class="container">
                    <tr>
                        <td>

                            <table class="row">
                                <tr>
                                    <td class="wrapper last">

                                        <table class="twelve columns">
                                            <tr>
                                                <td>
                                                    <h1>Congratulations {{ $user->name or 'NAME' }}</h1>
                                                    <p class="lead">You've purchased <a href="@if(isset($product)) {{ URL::action('BuyController@create', $product->id) }} @else 'LINK TO PRODUCT' @endif"> {{ $product->title or 'PRODUCT TITLE' }}</a></p>
                                                    <p>If you have any questions, please contact the seller <b>{{{ $seller->name or 'SELLER NAME' }}}</b> at <a href="mailto:{{ $seller->email or 'SELLER EMAIL'}}">{{ $seller->email or 'SELLER@EMAIL'}}</a></p>
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <table class="row callout">
                                <tr>
                                    <td class="wrapper last">

                                        <table class="twelve columns">
                                            <tr>
                                                <td class="panel">
                                                    {{ $product->description or 'PRODUCT DESCRIPTION' }}
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <table class="row footer">
                                <tr>
                                    <td class="wrapper">

                                        <table class="six columns">
                                            <tr>
                                                <td class="left-text-pad">

                                                    <h5>Ship to:</h5>
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <p>{{{ $ipn->address_name or 'SHIPPING NAME' }}}</p>
                                                                <p>{{{ $ipn->address_street or '000 PLACEHOLDER ST' }}}, {{{ $ipn->address_city or 'CITY' }}}</p>
                                                                <p>{{{ $ipn->address_state or 'STATE' }}}, {{{ $ipn->address_zip or 'ZIPCODE' }}}</p>
                                                                <p>{{{ $ipn->address_country or 'COUNTRY' }}}</p>
                                                            </td>
                                                        </tr>
                                                    </table>


                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                    <td class="wrapper last">

                                        <table class="six columns">
                                            <tr>
                                                <td class="last right-text-pad">
                                                    <h5>Payment</h5>
                                                    <p>Shipping price: {{ $ipn->mc_shipping or 'N.NN' }} {{ $ipn->mc_currency or 'ZZZ' }}</p>
                                                    <p><b>Total paid:</b> {{ $ipn->mc_gross or 'N.NN'}} {{ $ipn->mc_currency or 'ZZZ' }}</p>
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>


                            <table class="row">
                                <tr>
                                    <td class="wrapper last">

                                        <table class="twelve columns">
                                            <tr>
                                                <td align="center">
                                                    <center>
                                                        <p style="text-align:center;"><a href="http://sailr.co">Visit Sailr</a> | <a href="mailto:support@sailr.co">Email support@sailr.co for help with Sailr</a>
                                                        <p>Sailr checkout identifier: {{ $checkout->id or 'ZZZ999' }}.</p>
                                                        <p>Sent at: {{ Carbon\Carbon::now()->toDayDateTimeString() }} {{ date_default_timezone_get() }}</p>
                                                    </center>
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <!-- container end below -->
                        </td>
                    </tr>
                </table>

            </center>
        </td>
    </tr>
</table>

@stop