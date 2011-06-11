/* 

Used to present the administrator with options in a multiplemultiple
This field_type allows the user to choose 1 or more from a set of choices

*/
var savedSelectionString;

function ShowList(fieldID,key){
        var choice = 'choice'+key+'_'+fieldID;
        var boxes = document.forms['UpdateForm'][choice].length;
        var optionsString   = getOptions(fieldID);
        var optionsArray    = optionsString.split(',');
        var selectionString = document.getElementById('d['+key+']['+fieldID+']').value;
        var selectionArray  = selectionString.split(',');
        var optionSelected;
        
        savedSelectionString = selectionString;
        
        newInnerHTML = '';
        for (x in optionsArray){
                optionSelected = '';
                for (y in selectionArray){
                        if (selectionArray[y] == optionsArray[x]){
                                document.forms['UpdateForm'][choice][x].checked=true;
                        }
                }
        }
        
        document.getElementById(key + '_' + fieldID + '_Choice').style.display = 'inline';
}

function SaveList(fieldID,key){
        var choice = 'choice'+key+'_'+fieldID;
        var boxes = document.forms['UpdateForm'][choice].length;
        var comma = "";
        var selectionString = "";
        for (i = 0; i < boxes; i++) {
                if (document.forms['UpdateForm'][choice][i].checked) {
                        selectionString+= comma + document.forms['UpdateForm'][choice][i].value;
                        comma = ',';
                }
        }
        document.getElementById('d['+key+']['+fieldID+']').value=selectionString;
        
        HideList(fieldID,key);

}

function RestoreList(fieldID,key){
        document.getElementById('d['+key+']['+fieldID+']').value=savedSelectionString;
        
        HideList(fieldID,key);

}

function HideList(fieldID,key){
        var selectionString = '';
        
        selectionString = document.getElementById('d['+key+']['+fieldID+']').value;
        if (selectionString == ''){
                selectionString = '(Click to Choose)';
        }
        document.getElementById(key + '_' + fieldID + '_Choice').style.display = 'none';
        document.getElementById(key + '_' + fieldID + '_Input').value = selectionString.substring(0,60) + '...';
        
}

function getOptions(fieldID){
        return AllOptions[fieldID];
}


