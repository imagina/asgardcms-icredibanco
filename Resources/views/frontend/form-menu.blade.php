@php
    $op = array('required' => 'required');
    $op2 = array('required' => 'required','class'=>'form-control form-control-sm');
    $op3 = array('class'=>'form-control form-control-sm');
    $opPrice = array('min' => 0,'class'=>'form-control form-control-sm');
@endphp

<!-- Modal -->
<div class="modal fade" id="credibancoModal" tabindex="-1" role="dialog" aria-labelledby="credibancoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['route' => ['icredibanco'], 'method' => 'post','name' => 'credibancoPayment']) !!}

          <div class="modal-header">
            <h5 class="modal-title" id="credibancoModalLabel">{{trans('icredibanco::frontend.title')}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
             <div class="container-fluid">

                <div class="row">
                    <div class="col-6">
                        {!! Form::normalInput('firstname',trans('icredibanco::frontend.form.name'), $errors,null,$op2) !!}
                    </div>
                    
                    <div class="col-6">
                        {!! Form::normalInput('lastname',trans('icredibanco::frontend.form.lastname'), $errors,null,$op2) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        {!! Form::normalInputOfType('email','email', 'Email', $errors,null,$op2) !!}
                    </div>
                    
                    <div class="col-6">
                        {!! Form::normalInput('telephone',trans('icredibanco::frontend.form.tel'), $errors,null,$op2) !!}
                    </div>
                </div>

               
                {!! Form::normalInputOfType('number','total', trans('icredibanco::frontend.form.total'), $errors,null,$opPrice) !!}


                <h4>{{trans('icredibanco::frontend.form.billing details')}}</h4>

                {!! Form::normalInput('payment_address_1',trans('icredibanco::frontend.form.address'), $errors,null,$op2) !!}

                <div class="row">
                    <div class="col-6">
                        {!! Form::normalInput('payment_city',trans('icredibanco::frontend.form.city'), $errors,null,$op2) !!}
                    </div>
                    
                    <div class="col-6">
                        {!! Form::normalInput('payment_postcode',trans('icredibanco::frontend.form.postal code'), $errors,null,$op2) !!}
                    </div>
                </div>

                <label>{{trans('icredibanco::frontend.form.country')}}</label>
                <div class="select">
                    <div class="form-group">
                      <select class="form-control form-control-sm" id="select_countries" name="payment_country" required>
                        <option value="0">{{trans('icredibanco::frontend.form.select')}}</option>
                      </select>
                    </div>
                </div>

                <label>{{trans('icredibanco::frontend.form.state province')}}</label>
                <div class="select">
                    <div class="form-group">
                      <select class="form-control form-control-sm" id="select_cities" name="payment_zone" required>
                        <option value="0">{{trans('icredibanco::frontend.form.select')}}</option>
                      </select>
                    </div>
                </div>

                <h4>{{trans('icredibanco::frontend.form.delivery address')}}</h4>

                <div class="form-check">
                  <input type="checkbox" checked id="CheckShipping" name="CheckShipping" value="1" class="form-check-input"  data-toggle="collapse" data-target="#shippingsForms" aria-expanded="false" aria-controls="shippingsForms">

                  <label class="form-check-label" for="CheckShipping">{{trans('icredibanco::frontend.form.same delivery billing')}} </label>
                </div>
                
                <div class="collapse" id="shippingsForms">
                  {!! Form::normalInput('shipping_address_1',trans('icredibanco::frontend.form.address'), $errors,null,$op3) !!}

                  <div class="row">
                      <div class="col-6">
                          {!! Form::normalInput('shipping_city',trans('icredibanco::frontend.form.city'), $errors,null,$op3) !!}
                      </div>
                      
                      <div class="col-6">
                          {!! Form::normalInput('shipping_postcode',trans('icredibanco::frontend.form.postal code'), $errors,null,$op3) !!}
                      </div>
                  </div>

                  <label>{{trans('icredibanco::frontend.form.country')}}</label>
                  <div class="select">
                      <div class="form-group">
                        <select class="form-control form-control-sm" id="select_countriesSHIPPING" name="shipping_country">
                          <option value="0">{{trans('icredibanco::frontend.form.select')}}</option>
                        </select>
                      </div>
                  </div>
                 
                  <label>{{trans('icredibanco::frontend.form.state province')}}</label>
                  <div class="select">
                      <div class="form-group">
                        <select class="form-control form-control-sm" id="select_cities_shipping" name="shipping_city">
                          <option value="0">{{trans('icredibanco::frontend.form.select')}}</option>
                        </select>
                      </div>
                  </div>
                </div>

            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('icredibanco::frontend.button.close')}}</button>
            <button type="submit" class="btn btn-primary" id="btnPay">{{trans('icredibanco::frontend.button.pay')}}</button>
          </div>

        {!! Form::close() !!}

    </div>
  </div>
</div>

@section('scripts-owl')

<script type="text/javascript">
    
  jQuery(document).ready(function($) {

      $("#btnPay").attr("disabled", "disabled");
      $("#CheckShipping").attr("checked", true);

      var urlC = "{{url('/api/ilocations/allmincountries')}}";
      var isoDef = "CO";

      var selPaymentCountries = "#select_countries";
      var selPaymentCities = "#select_cities";

      var selShippingCountries = "#select_countriesSHIPPING";
      var selShippingCities = "#select_cities_shipping";

      var idMenu = "#{{$idMENU}}";
      var nLI = {{$nLI}};

      /****** Init ******/
     
      $(idMenu+" ul li:nth-child("+nLI+")").addClass("op-credibanco");
      $(".op-credibanco a").attr("data-toggle", "modal");
      $(".op-credibanco a").attr("data-target", "#credibancoModal");
     
      
      callCountries(urlC,isoDef,selPaymentCountries,1);
      callCities(isoDef,selPaymentCities);

      callCountries(urlC,isoDef,selShippingCountries,1);
      callCities(isoDef,selShippingCities);


      /***** Bugs ******/

      if(!($("#CheckShipping").is(':checked'))) {  
        $("#shippingsForms").addClass("show");
      } 


      /****** Events ******/

      $(selPaymentCountries).change(function() {

        countryISO = $(this).val();
        callCities(countryISO,selPaymentCities);
        
      });


      $(selShippingCountries).change(function() {

        countryISO = $(this).val();
        callCities(countryISO,selShippingCities);
        
      });

      
      $('#CheckShipping').change(function() {
        
        if(this.checked) {

          $("#shipping_address_1").removeAttr("required");
          $("#shipping_city").removeAttr("required");
          $("#shipping_postcode").removeAttr("required");
          $(selShippingCountries).removeAttr("required");
          $(selShippingCities).removeAttr("required");

        }else{

          $("#shipping_address_1").attr("required", "required");
          $("#shipping_city").attr("required", "required");
          $("#shipping_postcode").attr("required", "required");
          $(selShippingCountries).attr("required", "required");
          $(selShippingCities).attr("required", "required");
        }
          
      });

      /****** Functions ******/

      function callCities(countryISO,selID){

        url = "{{url('/api/ilocations/allprovincesbycountry/iso2')}}/"+countryISO;
        
        defaultISO = "";
        callCountries(url,defaultISO,selID,2);

      }

      function callCountries(url,defaultISO,selID,valueOP){

        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            beforeSend: function(){ 
              $("#btnPay").attr("disabled", "disabled");
            },
            success: function(data) {
                var sel = $(selID);
                var cIso = defaultISO;
                
                sel.empty();

                for (var i=0; i<data.length; i++) {

                    if(data[i].iso_2==cIso)
                        var yeh = "selected";
                    else
                        var yeh = "";

                    if(valueOP==1)
                      vOP = data[i].iso_2;
                    else
                      vOP = data[i].name;

                    sel.append('<option value="' + vOP + '"'+yeh+'>' + data[i].name +'</option>');
                    
                }

                $("#btnPay").removeAttr("disabled");
            },
            error: function(data){      
              console.log('Error:', data);    
            }
        });            
        
      }
       
          

  });

</script>


@stop

