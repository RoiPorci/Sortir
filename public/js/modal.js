function modifyModal(title, message){
    let modalTitle = document.getElementById('modal-info-title');
    modalTitle.innerText = title;
    let modalBody = document.getElementById('modal-info-body');
    modalBody.innerText = message;
}

function launchModal(){
    let btnModal = document.getElementById('launch-modal-info');
    btnModal.dispatchEvent(new MouseEvent('click'));
}

function sendInfoModal(title, message){
    modifyModal(title, message);
    launchModal();
}