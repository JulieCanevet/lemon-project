function getUserCountry () {
    // Call api with user ip address then get user data
    $.ajax('http://ip-api.com/json')
    .then(
        function success(response) {
            $('#form_Country option').filter(function(){
                return this.value == response.countryCode;
            }).prop("selected", true);
        },
        function fail(data, status) {
            console.log('Request failed');
        }
    );
  }
  getUserCountry()