<script type="text/javascript">
	var $LC_SURE_DELETE_LICENCE = 'Are you sure you want to delete this/these licence(s)?';
	var $LC_SURE_DELETE_MULTI_LICENCE = 'All corresponding Client (multi-tenant) licences will also be removed. Are you sure you want to delete this/these licence(s)?';

	function subtraction(el, min) {
		el = $(el);

		if ((parseInt(el.value) - 1) >= min) {
			el.value = parseInt(el.value) - 1;
		}

	}
	function addition(el, key, hostingKey) {
		el = $(el);

		if(hosting_count.hasOwnProperty(hostingKey))
        {
            var max = hosting_count[hostingKey];

            if (max != 0)
            {
                $$('input.object_count').each(function (input) {

                    if(input.getAttribute('data-parent-licence') == hostingKey)
                    {
                        max -= input.value;
                    }
                });
            }
            else max = 1;

            if (max > 0) el.value = parseInt(el.value) + 1;
        }

	}
	function lic_attach(p_lic_id) {

		new Ajax.Request('?req=licences&licence_id=' + p_lic_id,
		{
		  parameters: { mandator: $('mandator_' + p_lic_id).value, action: 'attach' },
		  onSuccess: function(response)  {
			  if (response.responseJSON.success)
			  {
			    var new_location = window.location.href;
			    new_location = new_location.split('&');
			    window.location = new_location[0];
			  }
			  else
			  {
				  alert(response.responseJSON.error);
			  }
		  }
		});
	}

	function licenceRemoval()
	{
	    if($$('#listHosts input[type="checkbox"]:checked').length === 0 && $$('#listTenants input[type="checkbox"]:checked').length === 0)
        {
            return;
        }

        $('licence_action').value = 'delete';
        if ($$('#listHosts input[type="checkbox"]:checked').length)
        {
            if (confirm($LC_SURE_DELETE_MULTI_LICENCE))
            {
                $('licence_action_multi_licence').value = '1';
                $('licence_form').submit();
            }
        }
        else if (confirm($LC_SURE_DELETE_LICENCE))
        {
            $('licence_form').submit();
        }
        $('licence_action').value = 'na';
    }

	var hosting_count =  {};

</script>


<div>
	<button type="button" class="btn bold" onclick="$('licences').fade({duration:0.3});new Effect.SlideDown('add-new',{duration:0.4});"><img src="../images/icons/silk/add.png" class="mr5" /><span>Install new licence</span></button>
	<button type="button" class="btn bold" onclick="licenceRemoval();"><img src="../images/icons/silk/delete.png" class="mr5" /><span>Remove selected licence</span></button>

	<img src="../images/ajax-loading.gif" style="margin-top:1px;margin-left:5px;display:none;" id="toolbar_loading" />
</div>

<hr class="separator" />

<form action="?req=licences" method="post" id="licence_form">
<input type="hidden" name="action" id="licence_action" value="na" />
<input type="hidden" name="multiLicenceAction" id="licence_action_multi_licence" value="0" />

[{if is_array($licences_single) && count($licences_single) > 0}]

	<h3>Subscription / Tenant Licenses</h3>

	<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10" id="listTenants">
		<colgroup>
			<col width="30" />
			<col width="180" />
			<col width="120" />
			<col width="180" />
			<col width="180" />
			<col width="180" />
			<col width="100" />
			<col width="100" />
		</colgroup>
		<thead>
			<tr>
				<th>&nbsp;[ ]</th>
				<th>Installed on tenant</th>
				<th>Licence Type</th>
				<th>Uploaded/Created</th>
				<th>Starts</th>
				<th>Expires</th>
				<th>Object limit</th>
				<th>in use</th>
				<th>free</th>
				<th>Add-ons</th>
			</tr>
		</thead>
		<tbody>
		[{* ------------------ Object calculations ------------------ *}]

		[{assign var="objects_in_use" value=0}]
		[{assign var="objects_distributed" value=0}]
		[{foreach from=$licences_single item=lic}]
		[{assign var="mandator" value=$lic.mandator}]

		[{if $lic.type eq 2}]
			[{if isset($lic.objcount)}]
				[{math equation="x+y" x=$lic.objcount y=$objects_distributed assign=objects_distributed}]
			[{/if}]
			[{if isset($lic.in_use)}]
				[{math equation="x+y" x=$lic.in_use y=$objects_in_use assign=objects_in_use}]
			[{/if}]
		[{/if}]

		[{* ------------------ Object calculations ------------------ *}]

		<tr class="[{cycle values="even,odd"}]" [{if ($lic.objcount-$lic.in_use) < 0 && !$lic.unlimited}]style="background-color:#ffcccc;"[{/if}]>
			<td><input type="checkbox" name="id[]" value="[{$mandator}],[{$lic.id}],client" /></td>
			<td>[{$mandators.$mandator|default:"<span class='red'>Deactivated tenant</span>"}]</td>
			<td>[{$lic.licencetype}] [{if $lic.parent_licence}](multi-tenant)[{/if}]</td>
			<td>[{$lic.uploaded|date_format:"%B %e, %Y"}]</td>
			<td>[{$lic.reg_date|date_format:"%A, %B %e, %Y"}]</td>
			<td>[{if $lic.expires}][{$lic.expires|date_format:"%A, %B %e, %Y"}][{else}]never[{/if}]</td>
			<td class="[{if $lic.exceeding}]redbg[{else}]greenbg[{/if}]" style="white-space:nowrap;">
				[{if strstr($lic.licencetype, "Hosting") || strstr($lic.licencetype, "Client") || $lic.parent_licence}]
				<div class="toolbar" style="display:inline;">
					<a class="bold" href="javascript:" onclick="subtraction('object_count_[[{$lic.id|cat:'_'|cat:$lic.parent_licence}]]', 1);">-</a>
					<input type="text" onblur="" style="width:52px;" name="object_count[[{$lic.id|cat:'_'|cat:$lic.parent_licence}]]" class="object_count" id="object_count_[[{$lic.id|cat:'_'|cat:$lic.parent_licence}]]" value="[{$lic.objcount}]" data-parent-licence="[{$lic.parent_licence}]" />
					<a class="bold" href="javascript:" onmousedown="addition('object_count_[[{$lic.id|cat:'_'|cat:$lic.parent_licence}]]', [[{$lic.id}]], '[{if $lic.parent_licence}][{$lic.parent_licence}][{else}]null[{/if}]');">+</a>
				</div>
				[{else}]
					[{if $lic.unlimited}]
						Unlimited
					[{else}]
						[{$lic.objcount}]
					[{/if}]
				[{/if}]
			</td>

			<td class="">[{$lic.in_use}]</td>
			<td class="bold[{if ($lic.objcount-$lic.in_use) < 0 && !$lic.unlimited}] red[{/if}]">
				[{if $lic.unlimited}]
					Unlimited
				[{else}]
					[{$lic.objcount-$lic.in_use}]
				[{/if}]
			</td>
			<td>[{implode(', ', array_keys($lic.data))}]</td>
		</tr>
		[{/foreach}]
		</tbody>
	</table>
[{else}]
<p class="note p10">Currently there are no multi-tenant licences installed on your system.</p>
[{/if}]

[{if is_array($licences_hosting) && count($licences_hosting) > 0}]
<h3>Multi-tenant</h3>

<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10" id="listHosts">
	<colgroup>
		<col width="30" />
		<col width="120" />
		<col width="125" />
		<col width="135" />
		<col width="180" />
		<col width="180" />
		<col width="100" />
	</colgroup>
	<thead>
		<tr>
			<th>&nbsp;[ ]</th>
			<th>Licence Type</th>
			<th>Uploaded</th>
			<th>Expires</th>
			<th>Object limit</th>
			<th>Distributed objects</th>
			<th>Objects in use</th>
			<th>Add-on</th>
			<th>Multi-tenant options</th>
		</tr>
	</thead>
	<tbody>
	[{foreach from=$licences_hosting item="lic"}]
	[{assign var="mandator" value=$lic.mandator}]
	<tr class="[{cycle values="even,odd"}]" [{if $objects_in_use > $lic.objcount && !$lic.unlimited}]style="background-color:#ffcccc;"[{/if}]>
		<td><input type="checkbox" class="hostingLicenses" name="id[]" value="0,[{$lic.id}],hosting" /></td>
		<td>[{$lic.licencetype}]</td>
		<td>[{$lic.uploaded|date_format:"%B %e, %Y"}]</td>
		<td>[{if $lic.expires}][{$lic.expires|date_format:"%A, %B %e, %Y"}][{else}]never[{/if}]</td>
		<td>
			[{if $lic.unlimited}]
				Unlimited
			[{else}]
				[{$lic.objcount}] ([{$lic.objcount-$objectsDistributed[$lic.id]}] free)

				[{if is_numeric($lic.objcount) && $lic.objcount > 0}]
					<script type="text/javascript">
						hosting_count['[{$lic.id}]'] = (hosting_count['[{$lic.id}]'] ? parseInt(hosting_count['[{$lic.id}]']): 0) + parseInt('[{$lic.objcount}]');
					</script>
				[{/if}]
			[{/if}]
		</td>
		<td>[{$objectsDistributed[$lic.id]}]</td>
		<td>[{$objectsInUse[$lic.id]}]</td>
		<td>[{implode(', ', array_keys($lic.data))}]</td>
		<td class="toolbar" style="line-height:30px;">
			[{if is_array($hosting_mandators) && count($hosting_mandators) > 0}]
			<select label="mandator" id="mandator_[{$lic.id}]">
				<optgroup label="Select tenant">
				[{foreach from=$hosting_mandators key=mandator_id item=mandator}]
					<option value="[{$mandator_id}]" label="[{$mandator}]">[{$mandator}]</option>
				[{/foreach}]
				</optgroup>
			</select>
			<a class="bold" href="javascript:" onclick="lic_attach('[{$lic.id}]');">
				<img src="../images/icons/plus-green.gif" style="vertical-align:middle;" border="0" alt="+" />
				Create tenant licence
			</a>
			[{else}]
			<span>No unlicenced tenants available</span>
			[{/if}]
		</td>
	</tr>
	[{/foreach}]
	</tbody>
</table>

<div class="toolbar">
	<a class="bold" href="javascript:" onclick="$('licence_action').value='save'; $('licence_form').submit();">Save changes</a>
</div>
[{/if}]
</form>

<script type="text/javascript">
	$$('input.object_count').invoke('on', 'keypress', function (ev) {
		if (ev.keyCode == Event.KEY_RETURN)
		{
			ev.preventDefault();
			$('licence_action').value='save';
			$('licence_form').submit();
		}
	});
</script>
