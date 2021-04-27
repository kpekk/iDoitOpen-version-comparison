<h3 class="p5 border-bottom border-grey gradient text-shadow">[{if $report_title}][{$report_title}][{else}][{isys type="lang" ident="LC__WIDGET__REPORTS"}][{/if}]</h3>

<div class="p5">
	[{if $error_message}]
		<p class="p5 mt5 [{if $friendly_error}]box-blue[{else}]box-red[{/if}]">[{if $friendly_error}]<img src="[{$dir_images}]icons/silk/information.png" class="vam" /> [{/if}]<span class="vam">[{$error_message}]</span></p>
	[{else}]
		[{if $report_description}]
		<table class="contentTable" style="border-top: none;">
			<tr>
				<td><p class="ml10 mr10">[{$report_description}]</p></td>
			</tr>
		</table>
		[{/if}]

		<div id="table_[{$unique_id}]" class="mt10"></div>
        [{if isset($compressedMultivalueCategories) && $compressedMultivalueCategories == 1}]
            <div class="mt10 p10 box-blue">
                <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" />
                [{isys type="lang" ident="LC__REPORT__VIEW__GROUPING_SORTING_HINT"}]
            </div>
        [{/if}]
		<script>
			[{include file=$report_js}]

			var reportData = '[{$report_json|escape:"javascript"}]'.evalJSON();

			if (reportData.length > 0)
			{
				new Lists.ReportList('table_[{$unique_id}]', {
					data:       reportData,
					filter:     "top",
					paginate:   "top",
					pageCount: [{$items_per_page|default:25}],
					draggable:  false,
					checkboxes: false,
                    // Disable sorting for compressed multivalue mode
                    [{if isset($compressedMultivalueCategories) && $compressedMultivalueCategories == 1}]
                    filter: false,
                    unsortedColumn: '[{$columnNames|escape:"javascript"}]'.evalJSON()
                    [{/if}]
				});
			}
		</script>
	[{/if}]
</div>

<style type="text/css">
	#table_[{$unique_id}] {
		overflow: hidden;
		overflow-x: auto;
	}
</style>