<?php

/* 
 * An overview table view of the fields with buttons to create a field or delete one.
 */

class FieldOverviewView extends AbstractView
{
    private $m_fields;
    
    
    public function __construct(Field ...$fields)
    {
        $this->m_fields = $fields;
    }
    
    public function renderContent()
    {
?>


<div class="container">
    
    <!-- Form for creating a new field -->
    <form action="/admin/field" method="POST">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Create Field</h4>
                <table class="table">
                    <tr>
                        <td><input id="field_name" name="field_name"></td>
                        <td><button class="btn btn-primary">Create Field</button></td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
    
    
    <br />
    
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Existing Fields</h4>
            <table class="table">
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
                <?php
                foreach ($this->m_fields as $field)
                {
                    /* @var $field Field */
                    ?>
                <tr>
                    <td><?= $field->get_name();?></td>
                    <td><a href="/admin/field/<?= $field->get_id(); ?>/delete" class="btn btn-danger">Delete</a></td>
                </tr>
                    <?php
                }
                ?>
            </table>
            <a href="/admin/field/create" class="btn btn-primary">Create Field</a>
        </div>
    </div>
</div>




<?php
    }
}