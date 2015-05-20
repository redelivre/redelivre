<?php

//Tentando limpar o código e fazer um reaproveitamento.


$mostrar = getPlataformSettings('MostrarPlanos');
$default_plan = getPlataformSettings('defaultPlan');

if( $mostrar == 'S')
{
	?>
	
	<tr class="form-field" style="display: none;">
		<th scope="row"><label for="plan_id">Selecione um plano</label></th>
		<td>
	
	<style type="text/css">
	.textcenter {
		text-align: center !important;
	}
	
	table#plans th,.feature {
		font-family: Arial, Verdana, Sans-serif;
		font-weight: bold !important;
		text-transform: uppercase;
	}
	
	table#plans th,table#plans td {
		border: 1px solid #efefef;
	}
	
	.valor {
		font-size: 16px !important;
		font-weight: bold;
	}
	</style>
	
			<table id="plans" class="clearfix">
				<thead class="clearfix">
					<th class="cel-4 textcenter"></th>
					<?php foreach (Plan::getAll() as $plan): ?>
					<th class="textcenter"><input type="radio" name="plan_id"
						class="radio" value="<?php echo $plan->id; ?>"
						<?php if ((isset($_POST['plan_id']) && $_POST['plan_id'] == $plan->id) || ( !array_key_exists('plan_id', $_POST) && $default_plan == $plan->id)) echo ' checked '; ?>>
						<?php echo $plan->name; ?></th>
					<?php endforeach; ?>
				</thead>
				<?php
				$priceFile = TEMPLATEPATH . '/includes/campaigns_prices.php';
	
				if (file_exists($priceFile)) {
					require $priceFile;
				}
				else {
					require MUCAMPANHAPATH.'/includes/campaigns_prices.php';
				}
				?>
			</table>
		</td>
	</tr>
	<?php
}
else 
{
?>
	<input type="hidden" name="plan_id" value="<?php echo wp_strip_all_tags($default_plan); ?>" />
<?php
} 
?>