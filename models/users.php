<?php
include_once __DIR__ . '/../app/config/db_connect.php';

class Users
 {
    static function login( $data = [] )
 {
        global $conn;

        $username = $data[ 'username' ];
        $password = $data[ 'password' ];

        $result = $conn->query( "SELECT * FROM users WHERE username = '$username'" );
        if ( $result = $result->fetch_assoc() ) {
            if ( $result[ 'status' ] === 'active' ) {
                $hashedPassword = $result[ 'password' ];
                $verify = password_verify( $password, $hashedPassword );
                if ( $verify ) {
                    unset( $result[ 'password' ] );
                    return $result;
                } else {
                    return false;
                }
            } else {
                unset( $result[ 'password' ] );
                return $result;
            }
        }
    }

    static function getUserByToken( $token )
 {
        global $conn;

        $result = $conn->query( "SELECT u.* FROM users u JOIN user_tokens ut ON u.id = ut.user_id WHERE ut.token = '$token' AND ut.expires_at > NOW()" );
        if ( $result && $result->num_rows > 0 ) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    static function register( $data = [] )
 {
        global $conn;

        $username = $data[ 'username' ];
        $email = $data[ 'email' ];
        $password = $data[ 'password' ];
        $role_id = $data[ 'role_id' ];

        $hashedPassword = password_hash( $password, PASSWORD_DEFAULT );
        $sql = 'INSERT INTO users SET username =?, password =?, email =?, role_id =?';
        $stmt = $conn->prepare( $sql );
        $stmt->bind_param( 'sssi', $username, $hashedPassword, $email, $role_id );
        $stmt->execute();

        if ( $stmt->affected_rows > 0 ) {
            $last_id = $conn->insert_id;
            return $last_id;
        } else {
            return false;
        }
    }

    static function findUserByUsernameOrEmail( $username, $email )
 {
        global $conn;

        $username = $conn->real_escape_string( $username );
        $email = $conn->real_escape_string( $email );

        $query = "SELECT username, email FROM users WHERE username = '$username' OR email = '$email' LIMIT 1";
        $result = $conn->query( $query );

        if ( $result->num_rows > 0 ) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    static function findUserByEmail( $email ) {
        global $conn;
        $sql = 'SELECT * FROM users WHERE email = ?';
        $stmt = $conn->prepare( $sql );
        $stmt->bind_param( 's', $email );
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    static function getPassword( $username )
 {
        global $conn;
        $sql = 'SELECT password FROM users WHERE username = ?';
        $stmt = $conn->prepare( $sql );
        $stmt->bind_param( 's', $username );
        $stmt->execute();

        $result = $stmt->affected_rows > 0 ? true : false;
        return $result;
    }
    static function isUsernameTaken( $username, $excludeUserId = null ) {
        global $conn;
        $sql = 'SELECT COUNT(*) as count FROM users WHERE username = ?';
        $params = [ $username ];

        if ( $excludeUserId ) {
            $sql .= ' AND id != ?';
            $params[] = $excludeUserId;
        }

        $stmt = $conn->prepare( $sql );
        if ( $stmt === false ) {
            return false;
        }

        $stmt->bind_param( str_repeat( 's', count( $params ) - 1 ) . 'i', ...$params );
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row[ 'count' ] > 0;
    }

    static function isEmailTaken( $email, $excludeUserId = null ) {
        global $conn;
        $sql = 'SELECT COUNT(*) as count FROM users WHERE email = ?';
        $params = [ $email ];

        if ( $excludeUserId ) {
            $sql .= ' AND id != ?';
            $params[] = $excludeUserId;
        }

        $stmt = $conn->prepare( $sql );
        if ( $stmt === false ) {
            return false;
        }

        $stmt->bind_param( str_repeat( 's', count( $params ) - 1 ) . 'i', ...$params );
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row[ 'count' ] > 0;
    }

    static function updateUser( $dataUser ) {
        global $conn;
        $setParts = [];
        $params = [];

        if ( isset( $dataUser[ 'username' ] ) ) {
            $setParts[] = 'username = ?';
            $params[] = $dataUser[ 'username' ];
        }
        if ( isset( $dataUser[ 'email' ] ) ) {
            $setParts[] = 'email = ?';
            $params[] = $dataUser[ 'email' ];
        }

        if ( empty( $setParts ) ) {
            return true;
        }

        $params[] = $dataUser[ 'id' ];

        $sql = 'UPDATE users SET ' . implode( ', ', $setParts ) . ' WHERE id = ?';
        $stmt = $conn->prepare( $sql );

        if ( $stmt === false ) {
            return false;
        }

        $stmt->bind_param( str_repeat( 's', count( $params ) - 1 ) . 'i', ...$params );
        $result = $stmt->execute();
        $stmt->close();

        $id = $dataUser[ 'id' ];
        $updatedData = $conn ->query( "SELECT * FROM users WHERE id ='$id'" );
        return $updatedData->fetch_assoc();
    }

    static function checkPassword( $data = [] ) {
        global $conn;

        $user_id = $data[ 'id' ];
        $password = $data[ 'password' ];
        $result = $conn->query( "SELECT * FROM users WHERE id = '$user_id'" );
        if ( $result = $result->fetch_assoc() ) {
            $hashedPassword = $result[ 'password' ];
            $verify = password_verify( $password, $hashedPassword );
            if ( $verify ) {
                return true;
            } else {
                return false;
            }
        }
    }

    static function getUserById( $id ) {
        global $conn;
        $sql = 'SELECT * FROM users WHERE id = ?';
        $stmt = $conn->prepare( $sql );
        $stmt->bind_param( 'i', $id );
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    static function getUsersData() {
        global $conn;
        $sql = 'SELECT role_id, COUNT(*) AS count FROM users WHERE role_id = 2 OR role_id = 3 GROUP BY role_id';
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->get_result();
        $labels = [];
        $data = [];
        if ( $result->num_rows > 0 ) {
            while ( $row = $result->fetch_assoc() ) {
                $labels[] = $row[ 'role_id' ];
                $data[] = $row[ 'count' ];
            }
        }
        return [ 'labels' => $labels, 'data' => $data ];
    }

    static function disableUser( $id ) {
        global $conn;
        $status = 'inactive';
        $sql = 'UPDATE users SET status = ? WHERE id = ?';
        $stmt = $conn->prepare( $sql );
        $stmt->bind_param( 'si', $status, $id );
        $success = $stmt->execute();
        $stmt->close();
        return true;
    }

    static function updateUserCode( $id, $code ) {
        global $conn;
        $sql = 'UPDATE users SET code = ? WHERE id = ?';
        $stmt = $conn->prepare( $sql );
        $stmt->bind_param( 'si', $code, $id );
        $stmt->execute();
        $stmt->close();
        return true;
    }

    static function checkVerificationCode( $email, $code ) {
        global $conn;
        $result = $conn->query( "SELECT * FROM users WHERE email = '$email'" );
        if ( $result->num_rows > 0 ) {
            $row = $result->fetch_assoc();
            $stored_code = $row['code'];
            if ( password_verify( $stored_code, $code ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    static function updatePassword( $data ) {
        global $conn;
        $password = $data['password'];
        $hashedPassword = password_hash( $password, PASSWORD_DEFAULT );
        $sql = 'UPDATE users SET password = ? WHERE email = ?';
        $stmt = $conn->prepare( $sql );
        $stmt->bind_param( 'ss', $hashedPassword, $data[ 'email' ] );
        $stmt->execute();
        $stmt->close();
        return true;
    }
}
