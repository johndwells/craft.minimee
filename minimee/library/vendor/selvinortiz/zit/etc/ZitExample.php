<?php
require __DIR__.'/ZitMocks.php';
require __DIR__.'/../vendor/autoload.php';

$zit = SelvinOrtiz\Zit\Zit::getInstance();

// Use bind() to add a serviceGenerator function to the container
// Only one instance of the service will be generated
$zit->bind( 'sessionService', function( $zit ) {
	return new SessionMock;
});

dump( $zit->sessionService(), 'First attempt to get an instance of SessionMock' );
dump( $zit->sessionService(), 'Second attempt to get an instance of SessionMock' );

// Use stash() to store a service within the container
// The service will be shared throughout your App
$cartService = new CartMock;
$zit->stash( 'cartService', $cartService );

dump( $zit->cartService(), 'First attempt to get an instance of CartMock' );
dump( $zit->cartService(), 'Second attempt to get an instance of CartMock' );
dump( SelvinOrtiz\Zit\Zit::cartService(), 'Third call made statically Zit::cartService()' );
// Use extend() to add callable functions to the container
$zit->extend( 'makeProduct', function() {
	return new ProductMock;
});

dump( $zit->makeProduct(), 'First call to makeProduct()' );
dump( $zit->makeProduct(), 'Second call to makeProduct()' );
dump( $zit->makeProduct(), 'Third call to makeProduct()' );

//------------------------------------------------------------

function dump( $data, $msg='' )
{
	echo '<pre>';
	echo $msg ? '<span style="color: #d00">'.$msg.'</span><hr>' : '';
	print_r( $data );
	echo '</pre>';
}
