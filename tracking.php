<?php
function mdt_tracking_func(){
	global $mdt;
	$docnum = isset($_REQUEST['docnum'])? $_REQUEST['docnum'] : '';
	/*
	Se lo shortcode produce molto HTML allora la funzione ob_start
	può essere usata per catturare l'output e convertirlo in una stringa nel modo seguente:
	*/
	ob_start();
	?>
	<div id="tracking-wrap">
		<div id="info" ></div>
		<form action="">
			<input name="docnum" type="text" value="<?php print_r($docnum === NULL? '':$docnum);?>" placeholder="tracking"></input>
			<input type="submit"></input>
		</form>
		
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>
		
		<script type="text/javascript">
			(function(doc){
				var translations = {					
						"docnum": "identificativo ordine",
						"cardCode": "",
						"brtTrakingId": "codice bartolini",
						"linkTrakingBrt": "link bartolini",
						"status": "stato ordine (1 inviato - 6 consegnato)",
						"orderData": "data ordine",
						"shippingMethod": false,
						"paymentmethod": false,
						"shippingAddress": "indirizzo di spedizione",
						"comments": "commenti",
						"shippingTotal": "spese di spedizione",
						"totalDiscount": "totale sconti",
						"total": "prezzo complessivo",
						"linkPayPal": false,
						"linkPaymentIWSmile": false,
						"products": "prodotti",
						"id": false,
						"name": "nome prodotto",
						"quantity": "quantità",
						"barcode": false,
						"price": "prezzo"
					}
				console.log(doc);
				return ( !doc )? undefined : document.querySelector('#info').innerHTML = (function objstr(argument, head) {
					str = '<table class="response-order-table table-'+head+'"><thead><tr><th colspan="2">'+head+'</th></tr></thead><tbody>'
					for(var i in argument){
						msg = (isNaN(Number(i))? (translations[i] || i).toLowerCase():String(Number(i) + 1));
						if(translations[i] === false) continue
						str += '<tr class="mdt-'+i+'">';
						str += (typeof argument[i] !== 'object')?
							'<td class="response-order-label">'
								+msg+
							'</td><td class="response-order-value">'+argument[i]+'</td>' : '<td class="response-order-label" colspan="2">'+objstr(argument[i], msg);
						str += '</td></tr>'
					}
					return str + '</tbody></table>';
				})(doc, 'Main');
			})( <?php $mdt->exec('getOrder', [$docnum], true) ?> );
			
		</script>
		<style type="text/css">
			.response-order-table th{
				text-align: right;
			}
			.response-order-table, .response-order-table tr{
				border:none;
			}
			.response-order-table td, .response-order-table th{
				margin: 0;
				padding: 0;
				padding-top: 1.5em;
				padding-bottom: 0.5em;
			}
			.response-order-label{
				font-weight: bold;
				color: #333;
			}
			.response-order-value{
				font-weight: normal;
				color: #333;
			}
		</style>
	</div>
	<?php
	return ob_get_clean();
}?>