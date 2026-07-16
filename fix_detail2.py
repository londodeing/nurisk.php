import re

with open('mobile/app/lib/features/operasi/insiden/presentation/screens/insiden_detail_screen.dart', 'r') as f:
    content = f.read()

content = content.replace('final int insidenId;', 'final String insidenUuid;')
content = content.replace('this.insidenId', 'this.insidenUuid')
content = content.replace('widget.insidenId', 'widget.insidenUuid')

old_when = """    return insidenAsync.when(
      loading: () => Scaffold(
        appBar: AppBar(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white),
        body: const Center(child: CircularProgressIndicator(color: Color(0xFF166534))),
      ),
      error: (e, _) => Scaffold(
        appBar: AppBar(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white, title: const Text('Error')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 8),
              Text(e.toString(), textAlign: TextAlign.center),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.read(insidenDetailProvider(widget.insidenUuid).notifier).refresh(),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
      data: (insiden) {"""

new_when = """    if (insidenAsync.isLoading) {
      return Scaffold(
        appBar: AppBar(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white),
        body: const Center(child: CircularProgressIndicator(color: Color(0xFF166534))),
      );
    }
    
    if (insidenAsync.error != null) {
      return Scaffold(
        appBar: AppBar(backgroundColor: const Color(0xFF166534), foregroundColor: Colors.white, title: const Text('Error')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 8),
              Text(insidenAsync.error!, textAlign: TextAlign.center),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.read(insidenDetailProvider(widget.insidenUuid).notifier).refresh(),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }
    
    final insiden = insidenAsync.insiden;
    if (insiden == null) return const SizedBox();
    
    {"""

content = content.replace(old_when, new_when)

# we must find `        );` just above `      },` and replace it
# actually, let's just use regex for the end of the data: (insiden) block
content = re.sub(r'(\s*);\s*},\s*\);\s*}\s*}', r'\1;\n  }\n}', content)

with open('mobile/app/lib/features/operasi/insiden/presentation/screens/insiden_detail_screen.dart', 'w') as f:
    f.write(content)

