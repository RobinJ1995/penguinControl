@extends ('system.website.vhost.create')

@section ('custom_fields')
	<div class="row">
		<div class="large-12 column">
			<label>
				<input type="checkbox" name="installTownCMS" value="true"/>
				Install Town CMS on this vHost
			</label>
		</div>
	</div>
@append
