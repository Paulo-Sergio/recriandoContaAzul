<?php

class Permissions extends Model {

    private $group;
    private $permissions;

    public function setGroup($id, $id_company) {
        // consultando quais os params que tem esse grupo
        $this->group = $id;
        $this->permissions = array();

        $sql = "SELECT params FROM permission_groups WHERE id = :id AND id_company = :id_company";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (empty($row['params'])) {
                $row['params'] = '0';
            }
            $params = $row['params'];

            /** consultando esses 'params' para saber quais os nomes
              e no final adicionar cada nome ao array de $this->permissions * */
            $this->searchingNameOfParameters($params, $id_company);
        }
    }

    private function searchingNameOfParameters($params, $id_company) {
        $sql = "SELECT name FROM permission_params WHERE id IN ($params) AND id_company = :id_company";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $p = $stmt->fetchAll();
            foreach ($p as $item) {
                $this->permissions[] = $item['name'];
            }
        }
    }

    public function hasPermission($name) {
        if (in_array($name, $this->permissions)) {
            return true;
        }
        return false;
    }

    public function getList($id_company) {
        $array = array();

        $stmt = $this->db->prepare("SELECT * FROM permission_params WHERE id_company = :id_company");
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $array = $stmt->fetchAll();
        }

        return $array;
    }

    public function getGroupList($id_company) {
        $array = array();

        $stmt = $this->db->prepare("SELECT * FROM permission_groups WHERE id_company = :id_company");
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $array = $stmt->fetchAll();
        }

        return $array;
    }

    public function getGroup($id, $id_company) {
        $array = array();

        $stmt = $this->db->prepare("SELECT * FROM permission_groups WHERE id = :id AND :id_company = id_company");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $array = $stmt->fetch();
            $array['params'] = explode(",", $array['params']);
        }

        return $array;
    }

    public function add($name, $id_company) {
        $sql = "INSERT INTO permission_params SET name = :name, id_company = :id_company";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->execute();
    }

    public function addGroup($name, $plist, $id_company) {
        $params = implode(",", $plist);

        $sql = "INSERT INTO permission_groups SET name = :name, id_company = :id_company, params = :params";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->bindParam(':params', $params);
        $stmt->execute();
    }

    public function editGroup($name, $plist, $id, $id_company) {
        $params = implode(",", $plist);

        $sql = "UPDATE permission_groups SET name = :name, id_company = :id_company, params = :params WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id_company', $id_company);
        $stmt->bindParam(':params', $params);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM permission_params WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function deleteGroup($id) {
        $u = new Users();

        // verifica se tem algum usuario associado a este grupo
        if ($u->findUsersInGroup($id) == false) {
            $sql = "DELETE FROM permission_groups WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }
    }

}
