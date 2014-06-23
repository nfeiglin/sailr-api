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
                                                    <h1>Oops... {{ $user->name or 'NAME' }}</h1>
                                                    <p class="lead">You've tried to purchase <a href="@if(isset($product)) {{ URL::action('BuyController@create', $product->id) }} @else 'LINK TO PRODUCT' @endif"> {{ $product->title or 'PRODUCT TITLE' }}</a> but we're afraid there have been some issues.</p>
                                                    <p class="panel">
                                                        {{ $errorReason or 'THE REASON OF THE ERROR GOES HERE' }}
                                                    </p>

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

                                                    <h5>Shipping details:</h5>
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <p>{{{ $ipn->getShipToName() }}}</p>
                                                                <p>{{{ $ipn->getAddress1() }}}, {{{ $ipn->getCity() }}}</p>
                                                                <p>{{{ $ipn->getState() }}}, {{{ $ipn->getZipCode() }}}</p>
                                                                <p>{{{ $ipn->getCountry() }}}</p>
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
                                                    <p>Shipping price: {{ $ipn->getShippingPrice() }} {{ $ipn->getCurrencyCode() }}</p>
                                                    <p><b>Total paid:</b> {{ $ipn->getGrossAmount() }} {{ $ipn->getCurrencyCode() }}</p>
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