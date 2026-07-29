@extends ('layout.master')

@section ('pageTitle')
Add page &bull; Staff
@endsection

@section ('js')
@parent
<script type="text/javascript" src="/js/ace/ace.js"></script>
@vite ('resources/js/page-editor.js')
@endsection

@section ('content')
<form action="/staff/page/create" method="POST" data-abide>
	<fieldset>
		<legend>Add page</legend>
		<div class="row">
			<div class="large-6 medium-12 small-12 column">
				<label>Title:
					<input type="text" name="title" value="{{ old ('title') }}" required />
				</label>
				<small class="error">Required field</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>Status:
					{{ Form::select
					(
						'published',
						array
						(
							'-1' => 'Draft',
							'0' => 'Published',
							'1' => 'Published with a menu link'
						),
						old ('published', '0')
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-6 small-12 column">
				<label>Weight:
					<input type="number" name="weight" value="{{ old ('weight', 0) }}" min="-127" max="127" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div>
			<label>Content (HTML):
				<div id="editor"></div>
				<textarea name="content" required>{{ old ('content') }}</textarea>
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection