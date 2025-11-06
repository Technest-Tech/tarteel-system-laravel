<!DOCTYPE html>
    <html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .fancy-button {
                background-color: #4CAF50; /* Green background */
                border: none; /* Remove borders */
                color: white; /* White text */
                padding: 15px 32px; /* Some padding */
                text-align: center; /* Centered text */
                text-decoration: none; /* Remove underline */
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                transition-duration: 0.4s; /* 0.4 second transition */
                cursor: pointer; /* Add a mouse pointer on hover */
            }

        </style>
    </head>
    <body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8" style="display: flex;align-items: center;justify-content: center">
                <div class="card" >
                    <h3 style="font-family: Calibri">Hello : {{$student->user_name ?? ''}} </h3>
                    <h3 style="font-family: Calibri">This is month {{$month}} billings amount</h3>
                    <h3 style="font-family: Calibri">Total Amount : {{$amount}} {{$currency}}</h3>
                    <div>
                        <a href="{{route('credit_custom.show',['currency'=>$currency ?? 0,'amount'=>$amount,'month'=>$month])}}" class="fancy-button" style="width:100%">XPay</a>
                    </div>
                    <div class="card-body">
                        <div id="paypal-button-container"  style="width: 150%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://www.paypal.com/sdk/js?client-id=AYd3vRCPCkLbVdZn83jrCH3cvdZi7dOa8YnhKuUHCmi4nbcv82DoeDdUWngu2NvgY_sC3t_2c1J_8bgF&components=buttons&currency={{$currency}}"></script>
<script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                // Set up the transaction
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '{{ $amount }}',
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                // Capture the funds from the transaction
                return actions.order.capture().then(function(details) {
                    // Redirect to success page or display success message
                    window.location.href = '/success/{{$month}}?student_id=0';
                });
            }
        }).render('#paypal-button-container');
    </script>
    </body>
    </html>


