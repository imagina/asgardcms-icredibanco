@php
    $op = array('required' => 'required');
@endphp

<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#credibancoModal">
  Pagar
</button>

<select id="test" name="test">
  <option value="1" selected>1</option>
  <option value="2">2</option>
</select>
<!-- Modal -->
<div class="modal fade" id="credibancoModal" tabindex="-1" role="dialog" aria-labelledby="credibancoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['route' => ['icredibanco'], 'method' => 'post','name' => 'credibancoPayment']) !!}

          <div class="modal-header">
            <h5 class="modal-title" id="credibancoModalLabel">PAGO CON CREDIBANCO</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
             <div class="container-fluid">

                <div class="row">
                    <div class="col-6">
                        {!! Form::normalInput('firstname','Nombre', $errors,null,$op) !!}
                    </div>
                    
                    <div class="col-6">
                        {!! Form::normalInput('lastname','Apellido', $errors,null,$op) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        {!! Form::normalInputOfType('email','email', 'Email', $errors,null,$op) !!}
                    </div>
                    
                    <div class="col-6">
                        {!! Form::normalInput('telephone','Telefono', $errors,null,$op) !!}
                    </div>
                </div>

               
                {!! Form::normalInput('total', 'Monto', $errors,null,$op) !!}


                <h4>Detalles de Facturacion</h4>

                {!! Form::normalInput('payment_address_1','Direccion', $errors,null,$op) !!}

                <div class="row">
                    <div class="col-6">
                        {!! Form::normalInput('payment_city','Ciudad', $errors,null,$op) !!}
                    </div>
                    
                    <div class="col-6">
                        {!! Form::normalInput('payment_postcode','Codigo Postal', $errors,null,$op) !!}
                    </div>
                </div>

                <label>Pais</label>
                <div class="select">
                    <div class="form-group">
                      <select class="form-control" id="select_countries" name="payment_country" required>
                        <option value="0">Seleccione</option>
                      </select>
                    </div>
                </div>

                <label>Estado / Provincia</label>
                <div class="select">
                    <div class="form-group">
                      <select class="form-control" id="select_cities" name="payment_city" required>
                        <option value="0">Seleccione</option>
                      </select>
                    </div>
                </div>

                <h4>Direccion de Entrega</h4>
                {{--
                {!! Form::normalInput('shipping_address_1','Direccion', $errors,null,$op) !!}

                <div class="row">
                    <div class="col-6">
                        {!! Form::normalInput('shipping_city','Ciudad', $errors,null,$op) !!}
                    </div>
                    
                    <div class="col-6">
                        {!! Form::normalInput('shipping_postcode','Codigo Postal', $errors,null,$op) !!}
                    </div>
                </div>

                <label>Pais</label>
                <div class="select">
                    <div class="form-group">
                      <select class="form-control" id="select_countries" name="shipping_country" required>
                        <option value="0">Seleccione</option>
                      </select>
                    </div>
                </div>

                <label>Estado / Provincia</label>
                <div class="select">
                    <div class="form-group">
                      <select class="form-control" id="select_countries" name="shipping_city" required>
                        <option value="0">Seleccione</option>
                      </select>
                    </div>
                </div>
                --}}




            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Pagar</button>
          </div>

        {!! Form::close() !!}

    </div>
  </div>
</div>

@section('scripts-owl')

<script type="text/javascript">
    
  jQuery(document).ready(function($) {
      
      url = "{{url('/api/ilocations/allmincountries')}}";

      url = "https://ecommerce.imagina.com.co/api/ilocations/allmincountries";

      $.ajax({
          type: "GET",
          url: url,
          dataType: "json",
          success: function(data) {
              //console.log(data);
              var sel = $("#select_countries");
              var cIso = "";
              //sel.empty();
              for (var i=0; i<data.length; i++) {

                  if(data[i].iso_2==cIso)
                      var yeh = "selected";
                  else
                      var yeh = "";

                  sel.append('<option value="' + data[i].iso_2 + '"'+yeh+'>' + data[i].name +'</option>');
                  
              }

              console.log("Cargaron los Paises");
          }
      })            
      .error( function(data) {
          console.log(data);
      });

      $("#test").change(function() {
        alert($(this).val());
      });

      /*
      $("#select_countries").on("change",function() {

        console.log("Select de paises");
          //alert($(this).val());

      });
      
      $("#select_cities").onchange/function() {

        console.log("Select de Ciudades");
          //alert($(this).val());

      });
      */

  });

</script>


@stop

