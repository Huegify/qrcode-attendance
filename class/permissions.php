<?php
include_once("response.php");
class Permissions extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->response = new Response();
    }

    public function permission_list($data)
    {
        $table_name    = "permissions";
        $primary_key   = "id";
        $columner = array(
            array('db' => 'id', 'dt' => 0),
           
            array('db' => 'action',  'dt' => 1, 'formatter' => function ($d, $row) {
                return ucfirst($d);
            }),
            array('db' => 'label',  'dt' => 2, 'formatter' => function ($d, $row) {
                return ucfirst($d);
            }),
            array('db' => 'operation_type',  'dt' => 3, 'formatter' => function ($d, $row) {
                return ucfirst($d);
            }),
            array('db' => 'description',  'dt' => 4, 'formatter' => function ($d, $row) {
                return ucfirst($d);
            }),
            array('db' => 'posted_user', 'dt' => 5, 'formatter' => function ($d, $row) {
                return $d;
            }),
            array('db' => 'posted_ip', 'dt' => 6, 'formatter' => function ($d, $row) {
                return $d;
            }),
            array('db' => 'is_deleted', 'dt' => 7, 'formatter' => function ($d, $row) {
                return ($d == 1) ? '<span class="badge bg-danger">Inactive</span>' : '<span class="badge bg-success">Active</span>';
            }),
            array('db' => 'created', 'dt' => 8, 'formatter' => function ($d, $row) {
                return date('d F, Y', strtotime($d));
            }),
            array('db' => 'id', 'dt' => 9, 'formatter' => function ($d, $row) {
                return "<a title='Edit Permissions' href=\"javascript:void(0)\" onclick=\"myLoadModal('setup/permission_setup?op=edit&id=$d','modal_div')\"><i  class='btn btn-primary fas fa-edit'></i></a>
                    <button title='Disable Permissions' class='btn btn-danger btn-sm delete delete-spin" . $d . "' data-title='" . ucfirst($row['action']) . "' data-id='" . $d . "'>
                       <span class='fas fa-trash-alt delete-icon" . $d . "'></span>
                       <span class='fa fa-spin fa-spinner deleting-icon" . $d . "' style='display: none;'></span>
                       </button>";
            })
        );
        // $filter = "";
        $filter = " AND is_deleted ='0' "; //remove deleted roles and Access Solutions Administrator from the lists of users to be rendered
        $datatableEngine = new engine();

        echo $datatableEngine->generic_table($data, $table_name, $columner, $filter, $primary_key);
    }

    public function loadActions($data)
    {
        $sql    = "SELECT * FROM menu WHERE parent_id = '#'";
        $result = $this->runQuery($sql);
        if ($result > 0) {
            $r = array();
            foreach ($result as $row) {
                $r[] = array($row['menu_id'], $row['menu_name']);
            }
            return $this->response->publishResponse(0, "parent menu found", $r, "array");
        } else {
            return $this->response->publishResponse("44", "No parent menu was found", "", "array");
        }
    }

    public function saveActions($data)
    {
        $role_id = $data['role_id'];
        $created = date('Y-m-d h:i:s');
        $posted_ip     = $_SERVER['REMOTE_ADDR'];
        $posted_user     = $_SESSION['username_sess'];
        $sql = "DELETE FROM permissions_map WHERE role_id = '$role_id'";
        $this->runQuery($sql, false);
        if (empty($data['menus'])) :
        else :
            foreach ($data['menus'] as $value) {
                $sql = "INSERT INTO permissions_map (role_id,permission_id, created, posted_ip, posted_user) VALUES('$role_id','$value', '$created', '$posted_ip', '$posted_user')";
                $this->runQuery($sql, false);
            }
        endif;
        return json_encode(array('response_code' => 0, 'response_message' => 'User Permissions has been saved successfully'));
    }

    public function loadPermissions($data)
    {
        $role_id = $data['role_id'];
        $visible = $this->visibleActions($role_id);
        $invisible = $this->inVisibleActions($role_id);
        return json_encode(array('response_code' => 0, 'response_message' => 'Menu has been created successfully', 'data' => array('visible' => $visible, 'invisible' => $invisible)));
    }

    private function visibleActions($role_id)
    {
        $sql     = "SELECT id,label FROM permissions WHERE is_deleted NOT IN (1) AND id IN (SELECT permission_id FROM permissions_map WHERE role_id = '$role_id') order by action";
        $result  = $this->runQuery($sql);
        // var_dump($sql);
        $visible = '';
        if ($result > 0) {
            foreach ($result as $key => $row) {
                $visible = $visible . '<div class="form-group single-role" draggable="true" ondragstart="drag(event)" id="tt' . $row['id'] . '">
                    <div id="text-content' . $row['id'] . '" onmouseover="hover(event)">' . $row['label'] . '</div>
                    <input type="hidden" name="menus[]" value="' . $row['id'] . '" class="form-group" />
                </div>';
            }
        }

        return $visible;
    }

    private function inVisibleActions($role_id)
    {
        $sql = "SELECT id,label FROM permissions WHERE is_deleted NOT IN (1) AND id NOT IN (SELECT permission_id FROM permissions_map WHERE role_id = '$role_id') order by action";
        $result  = $this->runQuery($sql);
        $invisible = '';
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $key => $row) {
                $invisible = $invisible . '<div class="form-group single-role" draggable="true" ondragstart="drag(event)" id="tt' . $row['id'] . '">
                    <div>' . $row['label'] . '</div>
                    <input type="hidden" name="menus[]" value="' . $row['id'] . '" class="form-group" />
                </div>';
            }
        }
        return $invisible;
    }

    public function deletePermission($data)
    {
        $id = $data['id'];
        $delete['is_deleted'] = 1;
        $stmt = $this->Update('permissions', $delete, array(''), array('id' => $id));
        if ($stmt > 0) :
            $array = array('response_code' => 0, 'response_message' => 'Record has been successfully deleted.');
        else :
            $array = array('response_code' => 20, 'response_message' => 'Record could not be deleted.');
        endif;
        echo json_encode($array);
    }

    public function savePermissions($data)
    {
        $data['posted_ip']     = $_SERVER['REMOTE_ADDR'];
        $data['posted_user']     = $_SESSION['role_id_sess'];

        if ($data['operation'] == "new") {
            $validation = $this->validate($data, array('label' => 'required|unique:permissions.label', 'operation_type' => 'required', 'action' => 'required'), array('label' => 'Operation Lable', 'operation_type' => 'Operation Type', 'action' => 'Operation (op)'));
            if (!$validation['error']) {
                $data['created'] = date('Y-m-d h:i:s');
                $count = $this->Insert('permissions', $data, array('op', 'operation', 'id', 'att-csrf-token-label'));
                if ($count > 0) {
                    return json_encode(array('response_code' => 0, 'response_message' => 'Permission Created Successfully'));
                } else {
                    return json_encode(array('response_code' => 22, 'response_message' => 'Permission Could not be Created'));
                }
            } else {
                return json_encode(array("response_code" => 29, "response_message" => $validation['messages'][0]));
            }
        } else {
            $data['lastmodified'] = date('Y-m-d h:i:s');
            $count = $this->Update('permissions', $data, array('op', 'operation', 'id', 'att-csrf-token-label'), array('id' => $data['id']));
            if ($count > 0) {
                return json_encode(array('response_code' => 89, 'response_message' => 'Permission Update Successfully', 'id'=>$data['id']));
            } else {
                return json_encode(array('response_code' => 22, 'response_message' => 'Permission Could not be Updated'));
            }
        }
    }

}
