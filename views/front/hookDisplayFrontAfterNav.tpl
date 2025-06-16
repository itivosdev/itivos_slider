{if !empty($sliders)} 
	{if $config_sliders.slider_mode eq "slider"}
		<div class="slider" >
			<ul class="slides">
				{foreach from=$sliders item=slider}
					{if !empty($slider.background)}
						<li>
							<div class="container">
								<div class="fondoElementosSlider">
									{if !empty($slider.title)}
										<h2>{$slider.title|capitalize}</h2>
									{/if}
									{if !empty($slider.title)}
										<p>{$slider.description}</p>
									{/if}
									{if isset($slider.call_to_action)}
										{if !empty($slider.call_to_action)}
											<a href="{$slider.call_to_action}" class="button button-primary">Ver más</a>
										{/if}
									{/if}
								</div>
							</div>
							<img src="{$url_site}{$slider.background}">
						</li>
					{/if}
				{/foreach}
			</ul>
		</div>
	{else}
		<div id="itivos_carousel" class="itivos_no_focus_slick">
			<div class="itivos_carousel_slick">
				{foreach from=$sliders item=$slider key=key}
					<img class="" 
						 src="{$url_site}{$slider.background}" 
					     	>
				{/foreach}
			</div>
		</div>
	{/if}
	<input type="hidden" name="slider_speed" value="{$config_sliders.slider_speed}" id="slider_speed">
{/if}