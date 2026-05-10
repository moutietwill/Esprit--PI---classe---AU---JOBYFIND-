<?php
class Utilisateur
{
    private ?int $id = null;
    private ?string $first_name = null;
    private ?string $last_name = null;
    private ?string $username = null;
    private ?string $date_of_birth = null;
    private ?string $phone = null;
    private ?string $city = null;
    private ?string $email = null;
    private ?string $password = null;
    private ?string $role = null;
    private ?string $status = null;
    private ?string $created_at = null;
    private ?string $last_login = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->first_name = $data['first_name'] ?? null;
            $this->last_name = $data['last_name'] ?? null;
            $this->username = $data['username'] ?? null;
            $this->date_of_birth = $data['date_of_birth'] ?? null;
            $this->phone = $data['phone'] ?? null;
            $this->city = $data['city'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->password = $data['password'] ?? null;
            $this->role = $data['role'] ?? null;
            $this->status = $data['status'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->last_login = $data['last_login'] ?? null;
        }
    }


    public function getId() { return $this->id; }
    public function getFirst_name() { return $this->first_name; }
    public function getLast_name() { return $this->last_name; }
    public function getUsername() { return $this->username; }
    public function getDate_of_birth() { return $this->date_of_birth; }
    public function getPhone() { return $this->phone; }
    public function getCity() { return $this->city; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }
    public function getCreated_at() { return $this->created_at; }
    public function getLast_login() { return $this->last_login; }


    public function setId($id) { $this->id = $id; }
    public function setFirst_name($first_name) { $this->first_name = $first_name; }
    public function setLast_name($last_name) { $this->last_name = $last_name; }
    public function setUsername($username) { $this->username = $username; }
    public function setDate_of_birth($date_of_birth) { $this->date_of_birth = $date_of_birth; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setCity($city) { $this->city = $city; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setRole($role) { $this->role = $role; }
    public function setStatus($status) { $this->status = $status; }
    public function setCreated_at($created_at) { $this->created_at = $created_at; }
    public function setLast_login($last_login) { $this->last_login = $last_login; }
}
?>
