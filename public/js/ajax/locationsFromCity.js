window.addEventListener('load', init);

function init(event){
    let selectCity = document.getElementById('trip_city');
    let selectLocation = document.getElementById('trip_location');
    let divLocation = document.getElementById('location-infos');

    selectCity.addEventListener('change', getCityDetails);
    selectLocation.addEventListener('change', setLocationDetails);

    if (selectLocation.value === ''){
        divLocation.setAttribute('style', 'visibility: hidden');
        selectLocation.setAttribute('disabled', 'disabled');
    } else {
        getCityDetails();
    }
}

function getCityDetails(){
    let selectCity = document.getElementById('trip_city');
    let data = {'cityId' : selectCity.value};

    fetch("create/api/city", {method: 'GET', body: JSON.stringify(data)})
        .then(function (response){
            return response.json();
        })
        .then(function (data){
            let city = JSON.parse(data);

            setCityDetails(city.zipCode);
            setSelectLocations(city.locations);
            setLocationDetails();
        });

}

function setCityDetails(zipCode){
    let spanZipCode = document.getElementById('zipCode');
    let selectCity = document.getElementById('trip_city');

    spanZipCode.innerText = zipCode;

    //On enlève le choix vide
    if (selectCity.options[0].innerText == ''){
        selectCity.options.remove(0);
    }

}

function setSelectLocations(locations) {
    let selectLocation = document.getElementById('trip_location');
    let divLocation = document.getElementById('location-infos');

    //Vider le select, le rendre actif et afficher les champs pour les infos
    selectLocation.innerText = '';
    selectLocation.removeAttribute('disabled');
    divLocation.setAttribute('style', 'visibility: visible');

    //Remplir le select
    locations.forEach((location) => {
        let option = document.createElement('option');

        option.value = location.id;
        option.innerText = location.name;
        option.dataset.street = location.street;
        option.dataset.latitude = location.latitude;
        option.dataset.longitude = location.longitude;

        selectLocation.insertAdjacentElement('beforeend', option);
    });
}

function setLocationDetails(){
    let selectLocation = document.getElementById('trip_location');
    let indexSelected = selectLocation.selectedIndex;
    let currentOption = selectLocation.options[indexSelected];
    let spanStreet = document.getElementById('street');
    let spanLatitude = document.getElementById('latitude');
    let spanLongitude = document.getElementById('longitude');

    //Remplir les données
    spanStreet.innerText = currentOption.dataset.street;
    spanLatitude.innerText = currentOption.dataset.latitude;
    spanLongitude.innerText = currentOption.dataset.longitude;
}