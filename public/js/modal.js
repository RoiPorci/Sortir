/**
 * modifies the info modal
 * @param title
 * @param message
 * @param type
 */
function modifyModal(title, message, type){
    let modalTitle = document.getElementById('modal-info-title');
    modalTitle.innerText = title;
    let modalBody = document.getElementById('modal-info-body');
    modalBody.innerText = message;
    let modalHeader = document.getElementById('modal-info-header');
    modalHeader.classList.remove('alert-danger');
    modalHeader.classList.remove('alert-success');
    modalHeader.classList.add('alert-'+type);
}

/**
 * launches the info modal by simualting a click on the launcher button
 */
function launchModal(){
    let btnModal = document.getElementById('launch-modal-info');
    btnModal.dispatchEvent(new MouseEvent('click'));
}

/**
 * modifies the info modal and launches it
 * @param title
 * @param message
 * @param type
 */
function sendInfoModal(title, message, type){
    modifyModal(title, message, type);
    launchModal();
}