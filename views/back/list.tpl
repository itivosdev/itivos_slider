<div class="main_app_trans">
	{if $sliders|count > 0}
		<div id="sortable_slider">
			{foreach from=$sliders item=$slider key=key}
				<div class="border_sldiers">
					<div class="slider_move">
						<span class="material-icons">
							open_with
						</span>
					</div>
					<div class="slider_content_itivos">
						<img class="slider_thumbs" slider_link="{$slider.id}" src="{$url_site}{$slider.background}">
					</div>
					<div class="buttons-slider-action">
						<a  class="boton border-boton" 
							href="{$url_site}{$admin_uri}/module/itivos_slider/sliders/show/sliders?id_slider={$slider.id}">
								<span class="material-icons">
								edit
								</span>
								Editar
						</a>
						<a class="boton border-boton confirm_link"
						   id_slider="{$slider.id}" 
						   title="Eliminar slider"
						   message="¿Realmente desea eliminar este slider? esta accíon es irreversible"
						   href="{$url_site}{$admin_uri}/module/itivos_slider/sliders/delete_slider?id_slider={$slider.id}">
							<span class="material-icons">
								delete
							</span>
							Borrar
						</a>
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
</div>
