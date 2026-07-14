import 'sdui_registry.dart';
import 'components/sdui_container.dart';
import 'components/sdui_list_view.dart';
import 'components/sdui_row.dart';
import 'components/sdui_column.dart';
import 'components/sdui_text.dart';
import 'components/sdui_icon.dart';
import 'components/sdui_card.dart';
import 'components/sdui_grid.dart';
import 'components/sdui_timeline.dart';
import 'components/sdui_bottom_sheet.dart';
import 'components/sdui_chart.dart';
import 'components/sdui_checkbox.dart';
import 'components/sdui_dialog.dart';
import 'components/sdui_dropdown.dart';
import 'components/sdui_form_field.dart';
import 'components/sdui_map.dart';
import 'components/sdui_scene.dart';
import 'components/sdui_switch.dart';
import 'components/sdui_tabs.dart';
import 'components/sdui_badge.dart';
import 'components/sdui_divider.dart';
import 'components/sdui_layout_primitives.dart';

class SduiRegistryInitializer {
  static void initialize() {
    final registry = SduiRegistry.instance;

    // Primitive Types
    registry.register('Container', (node) => SduiContainer(node: node));
    registry.register('Row', (node) => SduiRow(node: node));
    registry.register('Column', (node) => SduiColumn(node: node));
    registry.register('Text', (node) => SduiText(node: node));
    registry.register('Icon', (node) => SduiIcon(node: node));
    registry.register('Card', (node) => SduiCard(node: node));
    registry.register('ListView', (node) => SduiListView(node: node));
    registry.register('Badge', (node) => SduiBadge(node: node));
    
    // Layout Constraints
    registry.register('Expanded', (node) => SduiExpanded(node: node));
    registry.register('Flexible', (node) => SduiFlexible(node: node));
    registry.register('SizedBox', (node) => SduiSizedBox(node: node));
    registry.register('AspectRatio', (node) => SduiAspectRatio(node: node));
    registry.register('Divider', (node) => SduiDivider(node: node));

    // Advanced Primitive Types
    registry.register('Grid', (node) => SduiGrid(node: node));
    registry.register('Timeline', (node) => SduiTimeline(node: node));
    registry.register('BottomSheet', (node) => SduiBottomSheet(node: node));
    registry.register('Chart', (node) => SduiChart(node: node));
    registry.register('Checkbox', (node) => SduiCheckbox(node: node));
    registry.register('Dialog', (node) => SduiDialog(node: node));
    registry.register('Dropdown', (node) => SduiDropdown(node: node));
    registry.register('FormField', (node) => SduiFormField(node: node));
    registry.register('Map', (node) => SduiMap(node: node));
    registry.register('Scene', (node) => SduiScene(node: node));
    registry.register('Switch', (node) => SduiSwitch(node: node));
    registry.register('Tabs', (node) => SduiTabs(node: node));
  }
}
