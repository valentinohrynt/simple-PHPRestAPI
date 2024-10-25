<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include_once './lib/PHPMailer/src/Exception.php';
include_once './lib/PHPMailer/src/PHPMailer.php';
include_once './lib/PHPMailer/src/SMTP.php';

function view( $page, $data = [] )
 {
    extract( $data );
    include 'resources/views/' . $page . '.php';
}

class Router
 {
    public static $urls = [];

    function __construct()
 {
        $url = implode(
            '/',
            array_filter(
                explode(
                    '/',
                    str_replace(
                        $_ENV[ 'BASEDIR' ],
                        '',
                        parse_url( $_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH )
                    )
                ),
                'strlen'
            )
        );

        if ( !in_array( $url, self::$urls[ 'routes' ] ) ) {
            header( 'Location: ' . BASEURL );
        }

        $call = self::$urls[ $_SERVER[ 'REQUEST_METHOD' ] ][ $url ];
        $call();
    }
    public static function url( $url, $method, $callback )
 {
        if ( $url == '/' ) {
            $url = '';
        }
        self::$urls[ strtoupper( $method ) ][ $url ] = $callback;
        self::$urls[ 'routes' ][] = $url;
        self::$urls[ 'routes' ] = array_unique( self::$urls[ 'routes' ] );
    }
}

function urlpath( $path )
 {
    require_once 'app/config/static.php';
    return BASEURL . $path;
}

function setFlashMessage( $type, $message )
 {
    if ( !isset( $_SESSION[ 'user' ] ) ) {
        $_SESSION[ 'guest_' . $type ] = $message;
    } else {
        $_SESSION[ $type . '_' . $_SESSION[ 'user' ][ 'id' ] ] = $message;
    }
}

function displayFlashMessages( $type )
 {
    if ( !isset( $_SESSION[ 'user' ] ) ) {
        $messageKey = 'guest_' . $type;
        if ( isset( $_SESSION[ $messageKey ] ) ) {
            echo '<div class="alert alert-' . $type . ' alert-dismissible fade show absolute" role="alert">';
            echo $_SESSION[ $messageKey ];
            echo '<button type="button" class="btn-close custom-close-button" data-bs-dismiss="alert" aria-label="Close">';
            echo '</button>';
            echo '</div>';
            unset( $_SESSION[ $messageKey ] );
        }
    } else {
        $messageKey = $type . '_' . $_SESSION[ 'user' ][ 'id' ];
        if ( isset( $_SESSION[ $messageKey ] ) ) {
            echo '<div class="alert alert-' . $type . ' alert-dismissible fade show absolute" role="alert">';
            echo $_SESSION[ $messageKey ];
            echo '<button type="button" class="btn-close custom-close-button" data-bs-dismiss="alert" aria-label="Close">';
            echo '</button>';
            echo '</div>';
            unset( $_SESSION[ $messageKey ] );
        }
    }
}

function compressToWebP( $source, $destination, $quality = 20 ) {
    $info = getimagesize( $source );
    if ( $info[ 'mime' ] == 'image/jpeg' ) {
        $image = imagecreatefromjpeg( $source );
    } elseif ( $info[ 'mime' ] == 'image/png' ) {
        $image = imagecreatefrompng( $source );
    } else {
        return false;
    }
    imagepalettetotruecolor( $image );
    return imagewebp( $image, $destination, $quality );
}

function isCurrentPage( $page )
 {
    return strpos( $_SERVER[ 'REQUEST_URI' ], '/'.$page ) !== false ? 'active' : '';
}

function sendCodeEmail( $email, $code ) {
    $mail = new PHPMailer( true );
    $url = urlpath( 'resetpassword?verification='.$code.'&email=' . $email );

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV[ 'SMTP_HOST' ];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV[ 'SMTP_USERNAME' ];
        $mail->Password   = $_ENV[ 'SMTP_PASSWORD' ];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV[ 'SMTP_PORT' ];

        $mail->setFrom( $_ENV[ 'SMTP_USERNAME' ], 'Jember FnB Loker' );
        $mail->addAddress( $email );

        $mail->isHTML( true );
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = 'Click this link to reset your password: ' . $url;

        $mail->send();
        return true;
    } catch ( Exception $e ) {
        return false;
    }
}