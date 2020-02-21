<?php
namespace ZeroCz\Admin;

/**
 * ZeroCz's login/register system
 *
 * @version 1.0.0
 * @author ZeroCz
 */

class Auth
{
    private $db;

    private $minecraft;

    public $errors = [];

    /**
     * SESSION data
     *
     * @var array
     */
    private $data = [
        'logged'   => false,
        'username' => null,
        'realname' => null,
        'uuid'     => null,
        'id'       => null,
        'group'    => null,
        'mc_group' => null
    ];

    public function __construct()
    {
        $this->db = Database::init()->db();
        $this->minecraft = new Minecraft();
    }

    public function isError() {
        if (!empty($this->errors)) return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    /**
     * @return bool Is user logged?
     */
    public function Logged()
    {
        if (isset($_SESSION['user']['logged']) && $_SESSION['user']['logged'] === true) {
            return true;
        }
        return false;
    }

    /**
     * Logout user
     */
    public static function Logout()
    {
        Session::destroy();
        System::Redirect('./index.php');
    }

    /**
     * @param string $username Username
     * @param string $password Password
     */
    public function login($username, $password, $captcha)
    {
        $val = new \Validation();

        $val->name('Uživatelské jméno')->value($username)->required();
        $val->name('Heslo')->value($password)->required();
        $val->name('CAPTCHA')->captcha($captcha);

        if(!$val->isSuccess()) {
            $this->errors[] = $val->getError();
            return false;
        }

        return $this->loginUser($username, $password);
    }

    private function loginUser($username, $password) {

        $this->db->query("USE `main_authme`");
        $result = $this->db->select("authme", "password",
            ["realname" => $username]
        );

        if (!count($result) > 0 || !$this->isValidPassword($password, $result[0])) {
            $this->errors[] = 'Neplatné údaje!';
            return false;
        }

        if (!$this->minecraft->hasGroup($username)) {
            $this->errors[] = 'Nedostatečná oprávnění!';
            return false;
        }

        $this->setSession($username);
        return true;
    }

    private function setSession($username) {
        $this->db->query("USE `main_authme`");
        $result =$this->db->select("authme", [
            "username",
            "realname",
            "id"
        ], [
            "realname" => $username
        ]);

        if (\count($result) > 0) {
            foreach ($result as $row) {
                $this->data['username'] = $row['username'];
                $this->data['realname'] = $row['realname'];
                $this->data['id'] =  (int) $row['id'];
            }

            $this->db->query("USE `main_online`");
            $result = $this->db->select("players", [
                "uuid",
                "group"
            ], [
                "name" => $this->data['realname']
            ]);

            if(\count($result) > 0) {
                foreach ($result as $row2) {
                    $this->data['mc_group'] = $row2['group'];
                    $this->data['uuid'] = $row2['uuid'];
                    $this->data['group'] = Config::get('groups')[$row2['group']];
                }
                $this->data['logged'] = true;
                session_regenerate_id();
                Session::pushArray($this->data);
            }
        }
    }

    /**
     * Check if password is valid
     * @param  string  $password
     * @param  string  $hash
     * @return boolean
     */
    private function isValidPassword($password, $hash)
    {
        $parts = explode('$', $hash);
        return count($parts) === 4 && $parts[3] === hash('sha256', hash('sha256', $password) . $parts[2]);
    }

    public function getAvatar($avatar = '')
    {
        if (empty($avatar)) {
            $avatar = Session::get('realname');
        }

        return "https://minotar.net/avatar/$avatar/64.png";
        //return empty($avatar) ? '//minotar.net/avatar/' . Session::get('realname') . '/64.png' : '//minotar.net/avatar/' . $avatar . '/64.png';
    }
}
