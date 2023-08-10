<?php

class Role extends Model
{
    public function role_list($data)
    {
        $table_name    = "role";
        $primary_key   = "role_id";
        $columner = array(
            array('db' => 'role_id', 'dt' => 0),
            array('db' => 'role_id', 'dt' => 1),
            array('db' => 'role_name',  'dt' => 2, 'formatter' => function ($d, $row) {
                return ucfirst($d);
            }),
            array('db' => 'role_enabled', 'dt' => 3, 'formatter' => function ($d, $row) {
                if ($d == 1) :
                    $status = 'Enabled';
                else :
                    $status = 'Disabled';
                endif;
                return $status;
            }),
            array('db' => 'created', 'dt' => 4, 'formatter' => function ($d, $row) {
                return date('d F, Y', strtotime($d));
            }),
            array('db' => 'role_id', 'dt' => 5, 'formatter' => function ($d, $row) {
                $status = $this->getitemlabel('role', 'role_id', $d, 'is_deleted');
                $status = ($status == 0) ? "<button class='btn btn-danger btn-sm delete delete-spin" . $d . "' data-type='disable' title='Disable' data-title='" . ucfirst($row['role_name']) . "' data-id='" . $d . "'>
                    <span class='fa fa-ban delete-icon" . $d . "'></span>
                    <span class='fa fa-spin fa-spinner deleting-icon" . $d . "' style='display: none;'></span>
                    </button>" : "<button class='btn btn-primary btn-sm delete delete-spin" . $d . "' data-type='enable' title='Enable' data-title='" . ucfirst($row['role_name']) . "' data-id='" . $d . "'>
                    <span class='fa fa-check delete-icon" . $d . "'></span>
                    <span class='fa fa-spin fa-spinner deleting-icon" . $d . "' style='display: none;'></span>
                    </button>";

                // return "<a href=\"javascript:void(0)\" onclick=\"getpage('views/role_setup?op=edit&id=$row[role_id]','page')\"><i class='btn btn-primary fas fa-edit'></i></a>
                // " . $status;
                return $status;
            })
        );
        // $filter = "";
        $filter = " AND is_deleted IN (0,1) AND role_id !='100'"; //remove deleted roles and Access Solutions Administrator from the lists of users to be rendered
        $datatableEngine = new engine();

        echo $datatableEngine->generic_table($data, $table_name, $columner, $filter, $primary_key);
    }

    public function delete_role($data)
    {
        $id = $data['id'];
        $delete = ($data['type'] == 'enable') ? 0 : 1;
        $enable = ($data['type'] == 'enable') ? 1 : 0;

        $disable['is_deleted'] = $delete;
        $disable['role_enabled'] = $enable;

        $stmt = $this->Update('role', $disable, array(''), array('role_id' => $id));
        if ($stmt > 0) :
            $array = array('response_code' => 0, 'response_message' => 'Record has been successfully ' . $data['type'] . 'd.');
        else :
            $array = array('response_code' => 20, 'response_message' => 'Record could not be ' . $data['type'] . 'd.');
        endif;
        echo json_encode($array);
    }
    public function saveRole($data)
    {
        $validation = $this->validate($data, array('role_name' => 'required', 'role_enabled' => 'required|int'), array('role_name' => 'Role Name', 'role_enabled' => 'Enable Role'));
        if (!$validation['error']) {
            $data['created'] = date('Y-m-d h:i:s');

            if ($data['operation'] == "new") {
                $data['role_id'] = str_pad($this->getnextid('role'), 3, "0000000000000000", STR_PAD_LEFT);
                // $data['role_enabled'] = "1";
                $count = $this->Insert('role', $data, array('op', 'operation', 'id', 'att-csrf-token-label'));
                if ($count > 0) {
                    return json_encode(array('response_code' => 0, 'response_message' => 'Role Created Successfully'));
                } else {
                    return json_encode(array('response_code' => 291, 'response_message' => 'Role Could not be Created'));
                }
            } else {
                $count = $this->Update('role', $data, array('att-csrf-token-label'), array('role_id' => $data['role_id']));
                if ($count > 0) {
                    return json_encode(array('response_code' => 0, 'response_message' => 'Role Update Successfully'));
                } else {
                    return json_encode(array('response_code' => 291, 'response_message' => 'Role Could not be Updated'));
                }
            }
        } else {
            return json_encode(array("response_code" => 34, "response_message" => $validation['messages'][0]));
        }
    }
    public function getNextRoleId()
    {
        $sql    = "select CONCAT('00',max(role_id) +1) as rolee FROM role";
        $result = $this->runQuery($sql);
        return $result[0]['rolee'];
    }
}
