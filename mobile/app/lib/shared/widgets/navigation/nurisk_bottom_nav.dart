import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../../../core/theme/nurisk_colors.dart';

const List<_NavItem> _navItems = [
  _NavItem(Icons.home_outlined, Icons.home),
  _NavItem(Icons.map_outlined, Icons.map),
  _NavItem(Icons.inventory_2_outlined, Icons.inventory_2),
  _NavItem(Icons.person_outline, Icons.person),
];

class _NavItem {
  final IconData outlined;
  final IconData filled;
  const _NavItem(this.outlined, this.filled);
}



class _LogoButton extends StatefulWidget {
  final VoidCallback onTap;

  const _LogoButton({required this.onTap});

  @override
  State<_LogoButton> createState() => _LogoButtonState();
}

class _LogoButtonState extends State<_LogoButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;
  late Animation<double> _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 180),
    );
    _scale = TweenSequence<double>([
      TweenSequenceItem(
        tween: Tween(begin: 1.0, end: 1.08)
            .chain(CurveTween(curve: Curves.easeInOut)),
        weight: 50,
      ),
      TweenSequenceItem(
        tween: Tween(begin: 1.08, end: 1.0)
            .chain(CurveTween(curve: Curves.easeInOut)),
        weight: 50,
      ),
    ]).animate(_ctrl);
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  void _onTap() {
    HapticFeedback.lightImpact();
    _ctrl.forward(from: 0.0);
    widget.onTap();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: _onTap,
      child: AnimatedBuilder(
        animation: _scale,
        builder: (context, child) {
          return Transform.scale(scale: _scale.value, child: child);
        },
        child: SizedBox(
          width: 36,
          height: 36,
          child: SvgPicture.asset(
            'assets/logo/logo.svg',
            fit: BoxFit.contain,
          ),
        ),
      ),
    );
  }
}

class NuriskBottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onItemTapped;

  const NuriskBottomNav({
    super.key,
    required this.currentIndex,
    required this.onItemTapped,
  });

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).padding.bottom;

    return ClipRRect(
      borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 24, sigmaY: 24),
        child: Container(
          height: 64 + bottomInset,
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.55),
            border: Border(
              top: BorderSide(color: Colors.white.withValues(alpha: 0.3)),
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.06),
                blurRadius: 20,
                offset: const Offset(0, -4),
              ),
            ],
          ),
          child: Padding(
            padding: EdgeInsets.fromLTRB(16, 4, 16, 4 + bottomInset),
            child: Row(
              children: [
                Expanded(
                  child: _NavBarItem(
                    item: _navItems[0],
                    isActive: 0 == currentIndex,
                    onTap: () => onItemTapped(0),
                  ),
                ),
                Expanded(
                  child: _NavBarItem(
                    item: _navItems[1],
                    isActive: 1 == currentIndex,
                    onTap: () => onItemTapped(1),
                  ),
                ),
                Expanded(
                  child: _LogoButton(onTap: () => onItemTapped(2)),
                ),
                Expanded(
                  child: _NavBarItem(
                    item: _navItems[2],
                    isActive: 3 == currentIndex,
                    onTap: () => onItemTapped(3),
                  ),
                ),
                Expanded(
                  child: _NavBarItem(
                    item: _navItems[3],
                    isActive: 4 == currentIndex,
                    onTap: () => onItemTapped(4),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _NavBarItem extends StatefulWidget {
  final _NavItem item;
  final bool isActive;
  final VoidCallback onTap;

  const _NavBarItem({
    required this.item,
    required this.isActive,
    required this.onTap,
  });

  @override
  State<_NavBarItem> createState() => _NavBarItemState();
}

class _NavBarItemState extends State<_NavBarItem>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;
  late Animation<double> _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 200),
    );
    _scale = Tween<double>(begin: 1.0, end: 0.88).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => _ctrl.forward(),
      onTapUp: (_) {
        _ctrl.reverse();
        widget.onTap();
      },
      onTapCancel: () => _ctrl.reverse(),
      behavior: HitTestBehavior.opaque,
      child: AnimatedBuilder(
        animation: _scale,
        builder: (context, child) {
          return Transform.scale(scale: _scale.value, child: child);
        },
        child: Center(
          child: AnimatedSwitcher(
            duration: const Duration(milliseconds: 200),
            switchInCurve: Curves.easeInOut,
            switchOutCurve: Curves.easeInOut,
            transitionBuilder: (child, anim) {
              return FadeTransition(
                opacity: anim,
                child: ScaleTransition(scale: anim, child: child),
              );
            },
            child: Icon(
              widget.isActive ? widget.item.filled : widget.item.outlined,
              key: ValueKey('nav_${widget.isActive}'),
              size: 28,
              color: widget.isActive
                  ? NuriskColors.primary600
                  : NuriskColors.neutral500,
            ),
          ),
        ),
      ),
    );
  }
}
