@extends ('layout.master')

@section ('pageTitle')
Edit page &bull; Staff
@endsection

@section ('js')
@parent
<script type="text/javascript" src="/js/ace/ace.js"></script>
<script type="text/javascript" src="/js/embedAce.js"></script>
@endsection

@section ('content')
<form action="/staff/page/{{ $page->name }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit page</legend>
		<div class="row">
			<div class="large-6 medium-12 small-12 column">
				<label>Title:
					<input type="text" name="title" value="{{ old ('title', $page->title) }}" required />
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
						old ('published', $page->published)
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-6 small-12 column">
				<label>Weight:
					<input type="number" name="weight" value="{{ old ('weight', $page->weight) }}" min="-127" max="127" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div>
			<label>Content (HTML):
				<div id="editor"></div>
				<textarea name="content" required>{{ old ('content', $page->content) }}</textarea>
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $page->name }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection
