<?php

/* 
 * The view for editing a rule group.
 */

class RuleGroupEditorView extends AbstractView
{
    private $m_ruleGroup;
    private $m_attributeRules;
    private $m_fields;
    
    
    public function __construct(RuleGroup $ruleGroup, array $fields, AttributeRule ...$attributeRules)
    {
        $this->m_ruleGroup = $ruleGroup;
        $this->m_attributeRules = $attributeRules;
        $this->m_fields = $fields;
    }
    
    
    public function renderContent()
    {
?>


    
    <div class="card">
        <div class="card-body">
            <p>
                <label>Rule Group Name: </label>
                <input id="rule-group-name" type="text" value="<?= $this->m_ruleGroup->get_name(); ?>" />
            </p>
            
            <h2>Columns</h2>
            <!-- table of attributeRule rows -->
            <table id="attribute-rules-table" class="table">
                <tr>
                    <th width="200px">Field</th>
                    <th width="200px">Attribute Rule Name</th>
                    <th width="400px">Regular Expression</th>
                    <th width="400px">Error Message</th>
                    <th width="200px">Actions</th>
                </tr>
            </table>
            
            <button onclick="addAttributeRule('','', '', '');" class="btn btn-secondary">Add Field</button>
            <button onclick="submitRuleGroup();" class="btn btn-primary">Save</button>
        </div>
    </div>
    <!-- end of card -->

<script>
    


/**
 * Handle the user clicking the button to add a column/attribute rule
 * @param {type} attributeName - the name of the attribute. e.g speed_limit
 * @param {type} regexp - the regular expression to check against the field/column
 * @param {type} description - text message to display to the user when the rule fails.
 * @returns {undefined}
 */
function addAttributeRule(field_id, attributeName, regexp, description)
{
    var table = document.getElementById("attribute-rules-table");
    
    var row = document.createElement("tr");
    
    // The field the attribute rule will be assigned to.
    var cell1 = document.createElement("td");
    var dropdown = buildFieldDropdownMenu(field_id);
    cell1.appendChild(dropdown);
    
    // Name of the attribute_rule
    var cell2 = document.createElement("td");
    cell2.setAttribute("nowrap", "nowrap");
    var nameElement = document.createElement("input");
    nameElement.setAttribute("name", "attribute_name");
    nameElement.value = attributeName;
    cell2.appendChild(nameElement);
    
    // Regular expression to validate against
    var cell3 = document.createElement("td");
    cell3.innerHTML = '<b>/^ &nbsp;<input name="attribute_regexp" type="text" value="' + regexp + '" />&nbsp; $/</b>';
    
    // Error message/description column
    var cell4 = document.createElement("td");
    cell4.innerHTML = '<textarea style="width:100%" name="attribute_description" value="' + description + '" />';
    
    // actions cell
    var cell5 = document.createElement("td");
    cell5.setAttribute("nowrap", "nowrap");

    var deleteButton = document.createElement("button");
    deleteButton.innerHTML = "Delete";
    deleteButton.setAttribute("class", "btn btn-danger");
    deleteButton.onclick = function() { table.removeChild(row); };
    
    cell5.appendChild(deleteButton);
    
    row.appendChild(cell1);
    row.appendChild(cell2);
    row.appendChild(cell3);
    row.appendChild(cell4);
    row.appendChild(cell5);
    
    table.appendChild(row);
}


function buildFieldDropdownMenu(selected_field_id)
{
    var fields = <?= json_encode($this->m_fields, JSON_UNESCAPED_SLASHES); ?>;
    
    var dropdown = document.createElement("select");
    dropdown.setAttribute("name", "field");
    
    for (i in fields)   
    {
        var field = fields[i];
        
        var field_name = field.name;
        var field_id = field.id;
        
        var option = document.createElement("option");
        option.value = field_id;
        option.innerHTML = field_name;
        
        if (typeof selected_field_id !== 'undefined')
        {
            if (selected_field_id == field_id)
            {
                option.selected = "selected";
            }
        }
        
        dropdown.appendChild(option);
    }
    
    return dropdown;
}


function onloadCallback()
{
    var attributeRules = <?= json_encode($this->m_attributeRules, JSON_UNESCAPED_SLASHES); ?>;
    
    for (let index in attributeRules)
    {
        var attributeRule = attributeRules[index];
        var regExp = attributeRule.regexp;
        regExp = regExp.replace("/^", '');
        regExp = regExp.replace('$/', '');
        addAttributeRule(attributeRule.field_id, attributeRule.name, regExp, attributeRule.description);
    }
}

$(document).ready(onloadCallback);


/**
 * Send the data to the backend by pulling from the page.
 * Remember that this is not actually a form.
 * @returns {undefined}
 */
function submitRuleGroup()
{
    var ruleGroupName = document.getElementById("rule-group-name").value;
    
    if (ruleGroupName === "")
    {
        alert("You need to fill in a name for the rule group.")
        return;
    }

    var attributes = [];
    
    // loop through the attribute rules and create name value pairs to post.    
    var rowCallback = function (i, row) {
        // reference all the stuff you need first
        var $row = $(row);
        $attributeNameField = $row.find('input[name*="attribute_name"]');
        
        if ($attributeNameField.length > 0)
        {
            $attributeFieldIdField     = $row.find('select[name*="field"]');
            $attributeRegexpField      = $row.find('input[name*="attribute_regexp"]');
            $attributeDescriptionField = $row.find('textarea[name*="attribute_description"]');
            
            var attributeName = $attributeNameField[0].value;
            var attributeRegExp = '/^' + $attributeRegexpField[0].value + '$/';
            var attributeDescription = $attributeDescriptionField[0].value;
            var fieldID = $attributeFieldIdField[0].value;
            
            var attributeObj = {
                "field_id" : fieldID,
                "name" : attributeName,
                "regexp" : attributeRegExp,
                "description" : attributeDescription
            };
        
            attributes.push(attributeObj);
        }
    };
    
    $('#attribute-rules-table tr').each(rowCallback);
    
    
    var ruleGroup = {
        "name" : ruleGroupName,
        "attributes" : attributes
    };
    
    
    
    var successCallback = function(){
        alert("Successfully created/edited rule group.");
    };
    
    <?php
        if ($this->m_ruleGroup->get_id() !== null)
        {
            $postUrl = '/admin/rule-group/' . $this->m_ruleGroup->get_id();
        }
        else
        {
            $postUrl = '/admin/rule-group';
        }
    ?>
    
    var config = {
        "type" : "POST",
        "url" : '<?= $postUrl; ?>',
        "data" : ruleGroup,
        "success" : successCallback,
        "dataType" : "json"
    };
    
    $.ajax(config);
}
</script>





<?php
    }
}