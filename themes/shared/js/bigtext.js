/* 

Used to present the administrator a textbox in which to fill in 
text for bigtext fields

*/
var savedTextString;

function ShowText(fieldID,key){
        var textString = document.getElementById('d['+key+']['+fieldID+']').value;
        
        savedTextString = textString;
        
        document.getElementById('text' + key + '_' + fieldID).value = textString;
        
        document.getElementById(key + '_' + fieldID + '_Text').style.display = 'inline';
}

function SaveText(fieldID,key){
        var textString = document.getElementById('text' + key + '_' + fieldID).value;
        
        document.getElementById('d['+key+']['+fieldID+']').value=textString;
        
        HideText(fieldID,key);

}

function RestoreText(fieldID,key){
        document.getElementById('d['+key+']['+fieldID+']').value=savedTextString;
        
        HideText(fieldID,key);

}

function HideText(fieldID,key){
        var textString = document.getElementById('d['+key+']['+fieldID+']').value;
        
        if (textString == ''){
                textString = '(Click to Edit)';
        }
        else{
                textString = textString.substring(0,25) + '...';
                textString = textString.replace(/(\n|\r)/g, " ");
        }
        document.getElementById(key + '_' + fieldID + '_Text').style.display = 'none';
        document.getElementById(key + '_' + fieldID + '_Input').value = textString;
        
}

