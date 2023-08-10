<?php 

require_once('pdf/tcpdf.php');
require_once('model/model.php');
class MYPDF extends TCPDF {
    public $db;
    public $type;
    public $file;
    public $school;
    public $path;
    public function __construct()
    {
        parent::__construct();
        $this->db = new Model();
        $this->type = isset($_SESSION['school_id'])?$this->db->getitemlabel('lfs_schools','school_id',$_SESSION['school_id'],'school_cat_id'):0;
        $this->school = isset($_SESSION['school_id'])?$this->db->getitemlabel('lfs_schools','school_id',$_SESSION['school_id'],'school_display_name'):PDF_HEADER_TITLE;
        $this->file = ($this->type == 1 or $this->type == 2)?'kh':(($this->type == 3 or $this->type == 4)?'fa':'logo');
        $this->path = 'img/'.$this->file.'.png';

    }
    //Page header
    public function Header() {
        // Logo
        if (count($this->pages) === 1):
            $date = date('F d, Y h:m:i a');
            $this->Cell(0, 15, $date, 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->Image($this->path, 40, 20, 20, '', 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
            $this->Ln(23);
            // Set font
            $this->SetFont('helvetica', 'B', 17);
            // Title
            $this->Cell(0, 25,$this->school , 0, false, 'C', 0, '', 0, false, 'M', 'M');
        endif;
        
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}