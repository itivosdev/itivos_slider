{extends file='admin_views/v1/pages/_partials/modules/module_config.tpl'}
{block name=config}
	<h3 class="h3_div">
		Configuración {$module_name}
	</h3>
	<div class="row">
		<div class="col-md-12">
			<form action="" method="POST"  enctype="multipart/form-data">
				<div class="entradasForms">
					<span>Modo Slider</span>
					<label class="switch">
						<input type="checkbox" checked="checked">
						<span class="checkSlider redondeadoCheck"></span>
					</label>
					<span>Modo Diapositiva</span><br>

					<label>Velocidad de transición</label>
					<input type="number" name="speed" min="1000">
					<span class="desc">Mili segundos</span>
				</div>
			</form>
		</div>
	</div>
{/block}

