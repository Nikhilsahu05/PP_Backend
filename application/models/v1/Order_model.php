<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->tables = [
        	'orders' => 'orders',
            'party'         => 'party'
        ];
    }

     public function insert($data) {
        $this->db->insert($this->tables['orders'], $data);
        return $this->db->insert_id();
    }

    public function get_order_data()
    {
    	 $this->db->select('id,order_number');
        $this->db->order_by($this->tables['orders'].'.id', 'DESC');
        $this->db->limit(1);
    	$query = $this->db->get($this->tables['orders']);
    	if ($query->num_rows() > 0) {
            $res= $query->result();
            $order_number=date('dmY').$res[0]->id+1;
        }else{
          $no=1;
          $order_number=date('dmY').$no;
        }
       return $order_number;
    }

     public function update_party($data, $id) {
        return $this->db->update($this->tables['party'], $data, ['id' => $id]);
    }
}