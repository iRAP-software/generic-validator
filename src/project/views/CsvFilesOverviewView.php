<?php

/* 
 * An overview table view of the fields with buttons to create a field or delete one.
 */

class CsvFilesOverviewView extends AbstractView
{
    private $m_fileTypes;
    
    
    public function __construct(CsvType ...$types)
    {
        $this->m_fileTypes = $types;
    }
    
    public function renderContent()
    {
?>


<div class="container">

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Existing CSV Specs</h4>
            <table class="table">
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
                <?php
                foreach ($this->m_fileTypes as $fileType)
                {
                    /* @var $fileType CsvType */
                    ?>
                <tr>
                    <td><a href="/admin/csv-type/<?= $fileType->get_id(); ?>"><?= $fileType->get_name();?></a></td>
                    <td><a href="/admin/csv-type/<?= $fileType->get_id(); ?>/delete" class="btn btn-danger">Delete</a></td>
                </tr>
                    <?php
                }
                ?>
            </table>
            <a href="/admin/csv-type/create" class="btn btn-primary">Create CSV Spec</a>
        </div>
    </div>
</div>




<?php
    }
}