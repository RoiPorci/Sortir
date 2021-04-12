window.addEventListener('load', init);

var city;

function init(event){
    let selectCity = document.getElementById('trip_city');
    let selectLocation = document.getElementById('trip_location');

    selectCity.addEventListener('change', loadLocations);
    selectLocation.addEventListener('change', setLocationDetails);

    if (selectLocation.value !== ''){
        setLocationDetails();
    }
    else{
       cleanLocationOptions();
    }
}

function loadLocations(){
    let selectCity = document.getElementById('trip_city');
    let cityId = selectCity.value;
    let url = "api/get-locations-from-"+cityId;

    if (selectCity.value !== ''){
        fetch(url, {method: 'POST'})
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                city = JSON.parse(data);

                let spanZipCode = document.getElementById('zipCode');
                spanZipCode.innerText = city.zipCode;

                createLocationsOption(city.locations);
            })
    }
    else {
        cleanLocationOptions();
    }
}

function createLocationsOption(locations){
    let selectLocation = document.getElementById('trip_location');

    //Vider le select
    selectLocation.innerText= '';

    //Remplir le select
    locations.forEach((location) => {
        let option = document.createElement('option');

        option.value = location.id;
        option.innerText = location.name;

        selectLocation.insertAdjacentElement('beforeend', option);
    });

    setLocationDetails();
    showLocationDetails();
}

function setLocationDetails(){
    let selectLocation = document.getElementById('trip_location');
    let spanStreet = document.getElementById('street');
    let spanLatitude = document.getElementById('latitude');
    let spanLongitude = document.getElementById('longitude');

    if (selectLocation.value !== ''){
        city.locations.forEach((location) => {
            if (location.id == selectLocation.value){
                spanStreet.innerText = location.street;
                spanLatitude.innerText = location.latitude;
                spanLongitude.innerText = location.longitude;
            }
        })
    }
    else {
        spanStreet.innerText = '';
        spanLatitude.innerText = '';
        spanLongitude.innerText = '';
    }
}

function showLocationDetails(){
    let selectLocation = document.getElementById('trip_location');
    let divLocation = document.getElementById('location-infos');

    //Rendre visible les détails + permettre la sélection des Lieux
    selectLocation.removeAttribute('disabled');
    divLocation.setAttribute('style', 'visibility: visible');
}

function cleanLocationOptions(){
    let selectLocation = document.getElementById('trip_location');
    let divLocation = document.getElementById('location-infos');

    //Vider le select
    selectLocation.innerText= '';

    //Cacher les détails + disable le select des Lieux
    divLocation.setAttribute('style', 'visibility: hidden');
    selectLocation.setAttribute('disabled', 'disabled');
}
