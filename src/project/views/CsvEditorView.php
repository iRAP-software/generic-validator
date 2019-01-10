<?php

/* 
 * The view for editing/creating a CSV file type/spec
 * This just specified the field order.
 */

class CsvEditorView extends AbstractView
{
    private $m_csvType;
    private $m_possibleFields;
    private $m_fieldAssignements;
    
    
    public function __construct(CsvType $csvType)
    {
        $this->m_csvType = $csvType;
        $this->m_possibleFields = FieldTable::getInstance()->loadAll();
        $this->m_fieldAssignements = array();
        
        if ($csvType->get_id() != null)
        {
            $fieldAssTable = FieldAssignmentTable::getInstance();
            $where = array('csv_type_id' => $csvType->get_id());
            $this->m_fieldAssignements = $fieldAssTable->loadWhereAnd($where);
        }
    }
    
    
    public function renderContent()
    {
?>


    
    <div class="card">
        <div class="card-body">
            <p>
                <label>CSV Type Name: </label>
                <input id="csv-type-name" type="text" value="<?= $this->m_csvType->get_name(); ?>" />
            </p>
            
            <h2>Fields</h2>
            <!-- table of attributeRule rows -->
            <table id="fields-table" class="table">
                <tr>
                    <th width="20px">Column Index</th>
                    <th width="200px">Field</th>
                </tr>
            </table>
            
            <button onclick="addFieldAssignment('','', '', '');" class="btn btn-secondary">Add Field</button>
            <button onclick="submitCsvType();" class="btn btn-primary">Save</button>
        </div>
    </div>
    <!-- end of card -->

<script>
    


/**
 * Handle the user clicking the button to add a column/attribute rule
 * @param {type} columnIndex - The order e.g. 1,2,3
 * @param {type} fieldID - the ID of the field that is assigned
 * @returns {undefined}
 */
function addFieldAssignment(columnIndex, fieldID)
{
    var table = document.getElementById("fields-table");
    
    var row = document.createElement("tr");
    var cell1 = document.createElement("td");
    cell1.innerHTML = "<p>" + columnIndex + "</p>";
    
    // The field the attribute rule will be assigned to.
    var cell2 = document.createElement("td");
    var dropdown = buildFieldDropdownMenu();
    cell2.appendChild(dropdown);
    
    
    // actions cell
    var cell3 = document.createElement("td");
    cell3.setAttribute("nowrap", "nowrap");

    var bumpUpButton = document.createElement("button");
    bumpUpButton.innerHTML = "&#x2191;";
    bumpUpButton.setAttribute("class", "btn btn-secondary");
    bumpUpButton.onclick = function() { 
        if (row.previousElementSibling != null)
        {
            var copy = row;
            var previousSibling = row.previousElementSibling;
            //table.removeChild(row);
            table.insertBefore(copy, previousSibling);
        }
    };
    
    var bumpDownButton = document.createElement("button");
    bumpDownButton.innerHTML = "&#x2193;";
    bumpDownButton.setAttribute("class", "btn btn-secondary");
    bumpDownButton.onclick = function() { 
        if (row.nextSibling != null)
        {
            if (row.nextSibling.nextSibling != null)
            {
                var copy = row;
                var nextSibling = row.nextSibling;
                //table.removeChild(row);
                table.insertBefore(copy, nextSibling.nextSibling);
            }
            else
            {
                var copy = row;
                var nextSibling = row.nextSibling;
                //table.removeChild(row);
                table.appendChild(copy);
            }
        }
    };

    var deleteButton = document.createElement("button");
    deleteButton.innerHTML = "Delete";
    deleteButton.setAttribute("class", "btn btn-danger");
    deleteButton.onclick = function() { table.removeChild(row); };
    
    cell3.appendChild(bumpUpButton);
    cell3.appendChild(bumpDownButton);
    cell3.appendChild(deleteButton);
    
    row.appendChild(cell1);
    row.appendChild(cell2);
    row.appendChild(cell3);
    
    table.appendChild(row);
}


function buildFieldDropdownMenu()
{
    var fields = <?= json_encode($this->m_possibleFields, JSON_UNESCAPED_SLASHES); ?>;
    
    var dropdown = document.createElement("select");
    dropdown.setAttribute("name", "field");
    
    for (i in fields)   
    {
        var field = fields[i];
        
        var field_name = field.name;
        var field_id = field.id;
        console.log(field_id);
        console.log(field_name);
        
        var option = document.createElement("option");
        option.value = field_id;
        option.innerHTML = field_name;
        dropdown.appendChild(option);
    }
    
    return dropdown;
}


function onloadCallback()
{
    var fieldAssignments = <?= json_encode($this->m_fieldAssignements, JSON_UNESCAPED_SLASHES); ?>;
    
    for (let index in fieldAssignments)
    {
        var fieldAssignment = fieldAssignments[index];
        addFieldAssignment(fieldAssignment.id);
    }
}

$(document).ready(onloadCallback);


/**
 * Send the data to the backend by pulling from the page.
 * Remember that this is not actually a form.
 * @returns {undefined}
 */
function submitCsvType()
{
    var ruleGroupName = document.getElementById("csv-type-name").value;
    
    if (ruleGroupName === "")
    {
        alert("You need to fill in a name for the CSV file.");
        return;
    }

    var fieldAssignments = [];
    
    // loop through the fields and create name value pairs to post.    
    var rowCallback = function (i, row) {
        var $row = $(row);
        $fieldDropdown  = $row.find('select[name*="field"]');
        
        if ($fieldDropdown.length > 0)
        {            
            var fieldID = $fieldDropdown[0].value;
            
            var assignmentObject = {
                "field_id" : fieldID
            };
        
            fieldAssignments.push(assignmentObject);
        }
    };
    
    $('#fields-table tr').each(rowCallback);
    
    
    var ruleGroup = {
        "name" : ruleGroupName,
        "field_assignments" : fieldAssignments
    };
    
    
    var successCallback = function(){
        alert("Successfully created/edited rule group.");
    };
    
    <?php
        if ($this->m_csvType->get_id() !== null)
        {
            $postUrl = '/admin/csv-type/' . $this->m_csvType->get_id();
        }
        else
        {
            $postUrl = '/admin/csv-type';
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