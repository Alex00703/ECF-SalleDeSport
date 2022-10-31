// set active navbar
var path = new URL(window.location).pathname.split('/')[1];
var items = document.getElementsByClassName("item-sidebar");
for(var i = 0; i < items.length; i++){
    if(items[i].href.includes(path == "" ? "accueil" : path)){
        items[i].className += " active";
    }
}

// clickable rows
var rows = document.getElementsByClassName("clickable-row");
for (var i = 0; i < rows.length; i++) {
    rows[i].addEventListener('click', function(){
        window.location = this.getAttribute('data-href');
    });
}

// custom popup validation (je ne voulais pas passer par une popup classique qui est assez moche on va pas se mentir)

var form = null;

var forms = document.getElementsByClassName('formConfirm');
var popup = document.getElementById("confirm-popup");

for (var i = 0; i < forms.length; i++) {
    forms[i].addEventListener('submit', (event) =>{
        event.preventDefault();
        form = forms[i-1];
        popup.classList.remove("none");

    });
}

function checker(result){
    if(result == true){
        if(form != null){
            form.submit();
        }else{
            alert('Vous ne pouvez pas faire ça')
        }
    }else{
        popup.className += "none";
    }
}

function searchBar() {
    var input, search, tbody, tr, td, i, txtValue;
    input = document.getElementsByClassName("searchbar")[0];
    search = input.value.toUpperCase();
    tbody = document.getElementById("body-table");
    tr = tbody.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0];
        txtValue = td.textContent || td.innerText;
        if (txtValue.toUpperCase().indexOf(search) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}

state = 0;

function filterState(type){
    text = document.getElementsByClassName('filtre')[0];
    choices = ['Tout', 'Actifs', 'Inactifs']
    if(state == choices.length-1) {
        state = 0;
    }else{
        state +=1;
    }
    text.innerText = choices[state];
    tbody = document.getElementById("body-table");
    tr = tbody.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        if(type == 'franchise'){
            td = tr[i].getElementsByTagName("td")[3];
        }else if(type == 'structure'){
            td = tr[i].getElementsByTagName("td")[4];
        }
        tr[i].style.display = "";
        switch(text.innerText){
            case 'Actifs':
                if(!td.textContent.includes('Actif')){
                    tr[i].style.display = "none";
                }
                break;
            case 'Inactifs':
                if(!td.textContent.includes('Inactif')){
                    tr[i].style.display = "none";
                }
                break;
            default:
                break;
        }
    }
    
}

function openNav() {
    document.getElementById("nav-resp").style.width = "100%";
}
  
function closeNav() {
    document.getElementById("nav-resp").style.width = "0%";
}