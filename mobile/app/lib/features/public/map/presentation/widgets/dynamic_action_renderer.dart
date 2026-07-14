import 'package:flutter/material.dart';

class DynamicActionRenderer extends StatelessWidget {
  final List<dynamic> actions;

  const DynamicActionRenderer({Key? key, required this.actions}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Wrap(
        spacing: 8.0,
        runSpacing: 8.0,
        children: actions.map((action) {
          final String actionName = action.toString();
          
          // A real app might map these strings to actual callbacks/routing
          // For now, it just renders buttons based on strings from the API
          return ElevatedButton(
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Action triggered: $actionName')),
              );
            },
            child: Text(actionName.toUpperCase()),
          );
        }).toList(),
      ),
    );
  }
}
