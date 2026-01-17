<?php
/**
 * Classe para gerenciamento CRUD de usuários
 * Compatível com PHP 5.3.29
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/PasswordHelper.php';

class UserManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista todos os usuários
     */
    public function getAllUsers() {
        try {
            $stmt = $this->db->query("
                SELECT id, email, name, role, cpf, telefone, empresa, endereco, 
                       bairro, cidade, estado, pais, telefone_comercial, cnpj, 
                       created_at, updated_at
                FROM users
                ORDER BY created_at DESC
            ");
            return array(
                'success' => true,
                'users' => $stmt->fetchAll()
            );
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao buscar usuários'
            );
        }
    }

    /**
     * Busca usuário por ID
     */
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, name, role, cpf, telefone, empresa, endereco, 
                       bairro, cidade, estado, pais, telefone_comercial, cnpj, 
                       created_at, updated_at
                FROM users
                WHERE id = :id
            ");
            $stmt->execute(array('id' => $id));
            $user = $stmt->fetch();

            if ($user) {
                return array(
                    'success' => true,
                    'user' => $user
                );
            }

            return array(
                'success' => false,
                'message' => 'Usuário não encontrado'
            );
        } catch (Exception $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao buscar usuário'
            );
        }
    }

    /**
     * Busca usuário por email
     */
    public function getUserByEmail($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, name, role, cpf, telefone, empresa, endereco, 
                       bairro, cidade, estado, pais, telefone_comercial, cnpj, 
                       created_at, updated_at
                FROM users
                WHERE email = :email
            ");
            $stmt->execute(array('email' => $email));
            $user = $stmt->fetch();

            if ($user) {
                return array(
                    'success' => true,
                    'user' => $user
                );
            }

            return array(
                'success' => false,
                'message' => 'Usuário não encontrado'
            );
        } catch (Exception $e) {
            error_log("Get user by email error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao buscar usuário'
            );
        }
    }

    /**
     * Cria novo usuário
     */
    public function createUser($data) {
        try {
            // Validar campos obrigatórios
            if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
                return array(
                    'success' => false,
                    'message' => 'Campos obrigatórios: email, password, name'
                );
            }

            // Verificar se email já existe
            $existing = $this->getUserByEmail($data['email']);
            if ($existing['success']) {
                return array(
                    'success' => false,
                    'message' => 'Este email já está cadastrado'
                );
            }

            // Validar senha
            if (strlen($data['password']) < 6) {
                return array(
                    'success' => false,
                    'message' => 'A senha deve ter no mínimo 6 caracteres'
                );
            }

            // Hash da senha usando PasswordHelper
            $hashedPassword = PasswordHelper::hash($data['password']);

            // Inserir usuário
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    email, password, name, role, cpf, telefone, empresa, 
                    endereco, bairro, cidade, estado, pais, telefone_comercial, cnpj
                ) VALUES (
                    :email, :password, :name, :role, :cpf, :telefone, :empresa,
                    :endereco, :bairro, :cidade, :estado, :pais, :telefone_comercial, :cnpj
                )
            ");

            $role = isset($data['role']) ? $data['role'] : 'user';
            $cpf = isset($data['cpf']) ? $data['cpf'] : null;
            $telefone = isset($data['telefone']) ? $data['telefone'] : null;
            $empresa = isset($data['empresa']) ? $data['empresa'] : null;
            $endereco = isset($data['endereco']) ? $data['endereco'] : null;
            $bairro = isset($data['bairro']) ? $data['bairro'] : null;
            $cidade = isset($data['cidade']) ? $data['cidade'] : null;
            $estado = isset($data['estado']) ? $data['estado'] : null;
            $pais = isset($data['pais']) ? $data['pais'] : null;
            $telefoneComercial = isset($data['telefone_comercial']) ? $data['telefone_comercial'] : null;
            $cnpj = isset($data['cnpj']) ? $data['cnpj'] : null;

            $stmt->execute(array(
                'email' => $data['email'],
                'password' => $hashedPassword,
                'name' => $data['name'],
                'role' => $role,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'empresa' => $empresa,
                'endereco' => $endereco,
                'bairro' => $bairro,
                'cidade' => $cidade,
                'estado' => $estado,
                'pais' => $pais,
                'telefone_comercial' => $telefoneComercial,
                'cnpj' => $cnpj
            ));

            $userId = $this->db->lastInsertId();

            // Buscar o usuário recém-criado com todos os campos
            $stmt = $this->db->prepare("
                SELECT 
                    id, email, password, name, role, 
                    cpf, telefone, empresa, endereco, bairro, 
                    cidade, estado, pais, telefone_comercial, cnpj,
                    created_at, updated_at
                FROM users 
                WHERE id = :id
            ");
            $stmt->execute(array('id' => $userId));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Remover senha da resposta por segurança
            if ($user) {
                unset($user['password']);
            }

            return array(
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'user' => $user
            );
        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao criar usuário'
            );
        }
    }

    /**
     * Atualiza usuário existente
     */
    public function updateUser($id, $data) {
        try {
            // Verificar se usuário existe
            $existing = $this->getUserById($id);
            if (!$existing['success']) {
                return $existing;
            }

            // Verificar se email está sendo alterado e se já existe
            if (isset($data['email']) && $data['email'] !== $existing['user']['email']) {
                $emailCheck = $this->getUserByEmail($data['email']);
                if ($emailCheck['success']) {
                    return array(
                        'success' => false,
                        'message' => 'Este email já está cadastrado para outro usuário'
                    );
                }
            }

            // Construir query dinamicamente
            $fields = array();
            $params = array('id' => $id);

            $allowedFields = array(
                'email', 'name', 'role', 'cpf', 'telefone', 'empresa',
                'endereco', 'bairro', 'cidade', 'estado', 'pais',
                'telefone_comercial', 'cnpj'
            );

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }

            // Atualizar senha se fornecida
            if (isset($data['password'])) {
                if (strlen($data['password']) < 6) {
                    return array(
                        'success' => false,
                        'message' => 'A senha deve ter no mínimo 6 caracteres'
                    );
                }
                $fields[] = "password = :password";
                $params['password'] = PasswordHelper::hash($data['password']);
            }

            if (empty($fields)) {
                return array(
                    'success' => false,
                    'message' => 'Nenhum campo para atualizar'
                );
            }

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return array(
                'success' => true,
                'message' => 'Usuário atualizado com sucesso'
            );
        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao atualizar usuário'
            );
        }
    }

    /**
     * Remove usuário
     */
    public function deleteUser($id) {
        try {
            // Verificar se usuário existe
            $existing = $this->getUserById($id);
            if (!$existing['success']) {
                return $existing;
            }

            // Remover usuário (cascata remove sessões e atividades)
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(array('id' => $id));

            return array(
                'success' => true,
                'message' => 'Usuário removido com sucesso'
            );
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Erro ao remover usuário'
            );
        }
    }
}
