@extends ('layout.master')

@section ('pageTitle')
Virtualisatiecluster &bull; Staff
@endsection

@section ('content')
<div class="row">
	<ul class="large-block-grid-3 medium-block-grid-2 small-block-grid-1 virt">
		@foreach ($nodes as $node)
		<li>
			<ul class="pricing-table">
				<li class="title">{{ ucfirst ($node->getName ()) }}</li>
				<li class="description">
					CPU: {{ $node->getCPUUsage () }}%
					<div class="progress {{ $node->getCPUUsage () < 20 ? 'success' : ($node->getCPUUsage () > 80 ? 'alert' : '') }} radius">
						<span class="meter" style="width: {{ $node->getCPUUsage () }}%"></span>
					</div>
				</li>
				<li class="description">
					RAM: {{ round ($node->getMem () / 1000000000, 3) }}/{{ round ($node->getMaxMem () / 1000000000, 3) }} GB
					<div class="progress {{ $node->getMemoryUsage () < 40 ? 'success' : ($node->getMemoryUsage () > 80 ? 'alert' : '') }} radius">
						<span class="meter" style="width: {{ $node->getMemoryUsage () }}%"></span>
					</div>
				</li>
				<li class="description">
					Opslag: {{ round ($node->getDisk() / 1000000000, 3) }}/{{ round ($node->getMaxDisk () / 1000000000, 3) }} GB
					<div class="progress {{ $node->getDiskUsage () < 40 ? 'success' : ($node->getDiskUsage () > 80 ? 'alert' : '') }} radius">
						<span class="meter" style="width: {{ $node->getDiskUsage () }}%"></span>
					</div>
				</li>
				<li class="description">
					Uptime: {{ $node->getUptimeDays () }} dagen
					<div class="progress {{ $node->getUptimeDays () > 100 ? 'success' : '' }} radius">
						<span class="meter" style="width: {{ $node->getUptimeDays () / ($node->getUptimeDays () > 365 ? $node->getUptimeDays () : 365) * 100 }}%"></span>
					</div>
				</li>
				@foreach ($node->getVMs () as $vm)
				<li class="bullet-item">
					<img src="/img/icons/virtualisation-vm-{{ $vm->isTemplate () ? 'template' : 'state-' . (in_array ($vm->getState (), array ('running', 'stopped')) ? $vm->getState () : 'unknown') }}.png" alt="" /> <span class="label secondary">{{ $vm->getId () }}</span> {{ $vm->getName () }}
					@if ($vm->getState () == 'running')
					<div class="row virt-vm">
						<div class="large-4 medium-4 small-4 column">
							<span class="label {{ $vm->getCPUUsage () < 20 ? 'success' : ($vm->getCPUUsage () > 80 ? 'alert' : '') }}">
								CPU:<br />
								{{ $vm->getCPUUsage () }}%
							</span>
						</div>
						<div class="large-4 medium-4 small-4 column">
							<span class="label {{ $vm->getMemoryUsage () < 40 ? 'success' : ($vm->getMemoryUsage () > 80 ? 'alert' : '') }}">
								RAM:<br />
								{{ $vm->getMemoryUsage () }}%
							</span>
						</div>
						<div class="large-4 medium-4 small-4 column">
							<span class="label {{ $vm->getDiskUsage () < 40 ? 'success' : ($vm->getDiskUsage () > 80 ? 'alert' : '') }}">
								Opslag:<br />
								{{ $vm->getDiskUsage () }}%
							</span>
						</div>
					</div>
					@endif
				</li>
				@endforeach
			</ul>
		</li>
		@endforeach
	</ul>
</div>
@endsection