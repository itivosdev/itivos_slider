<div class="main_app">
	<a href="{$url_site}{$admin_uri}/modules/config/itivos_slider?btnAdd=1" class="right">
		<i class="material-icons edit_menu">
			add
		</i>
	</a>
	<h3 class="h3_div">Listado de diapositivas</h3>
	{if $sliders|count > 0}
		<div class="row">
			<div id="sortable_slider">
				{foreach from=$sliders item=$slider key=key}
					<div class="col-md-12 border_sldiers">
						<div class="slider_move col-md-3">
							<span class="material-icons">
								open_with
							</span>
						</div>
						<div class="slider_content_itivos col-md-3">
							<img class="slider_thumbs" slider_link="{$slider.id}" src="{$url_site}{$slider.background}">
						</div>
						<div class="col-md-6 buttons-slider-action">
							<a  class="boton border-boton" 
								href="{$url_site}{$admin_uri}/modules/config/itivos_slider?id_slider={$slider.id}">
									<span class="material-icons">
									edit
									</span>
									Editar
							</a>
							<a class="boton border-boton del_slider"
							   id_slider="{$slider.id}" 
							   href="#!">
								<span class="material-icons">
									delete
								</span>
								Borrar
							</a>
						</div>
					</div>
				{/foreach}
			</div>
		</div>
	{/if}
</div>