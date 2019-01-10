<?php

/* 
 * 
 */

class RuleGroupOverviewView extends AbstractView
{
    private $m_ruleGroups;
    
    
    public function __construct(RuleGroup ...$ruleGroups)
    {
        $this->m_ruleGroups = $ruleGroups;
    }
    
    public function renderContent()
    {
?>


<div class="container">
    

    <div class="card">
        <div class="card-body">
            <table class="table">
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
                <?php
                foreach ($this->m_ruleGroups as $ruleGroup)
                {
                    /* @var $ruleGroup RuleGroup */
                    ?>
                <tr>
                    <td><a href="/admin/rule-group/<?= $ruleGroup->get_id(); ?>"><?= $ruleGroup->get_name();?></a></td>
                    <td><a href="/admin/rule-group/<?= $ruleGroup->get_id(); ?>/delete" class="btn btn-danger">Delete</a></td>
                    
                </tr>
                    <?php
                }
                ?>
            </table>
            <a href="/admin/rule-group/create" class="btn btn-primary">Create</a>
        </div>
    </div>
</div>




<?php
    }
}