# SDUI LIFECYCLE REPORT

## 1. SDUI Lifecycle Flow
Below is the sequential call graph of the SDUI lifecycle in the NURISK application:

```mermaid
sequenceDiagram
    autonumber
    participant App as Flutter App (Client)
    participant BFF as BFF Server (Laravel)
    
    Note over App: 1. BOOTSTRAP
    App->>App: ensureInitialized() & register components
    App->>App: RuntimeInitializer & Route Initial Location
    
    Note over App: 2. LOGIN / SESSION RESTORE
    App->>App: Load token from SecureStorage
    
    Note over App: 3. REQUEST JSON
    App->>BFF: GET bff/dashboard OR account/home
    BFF-->>App: Return JSON payload
    
    Note over App: 4. DESERIALIZE (FAILURE POINT)
    rect rgb(240, 200, 200)
    App->>App: Map JSON to Model (ConfigModel / AccountHomeData)
    Note right of App: Mismatch: payload keys (widgets/cards) vs expected keys (nodes).<br/>Resulting list of nodes is parsed as [].
    end
    
    Note over App: 5. REGISTRY LOOKUP
    App->>App: SduiRenderer queries SduiRegistry for builders
    
    Note over App: 6. RENDERER
    App->>App: Build SduiScreen with empty nodes list
    
    Note over App: 7. WIDGET TREE
    App->>App: Scaffold -> SingleChildScrollView -> Column(children: [])
    
    Note over App: 8. FRAME & PAINT & GPU
    App->>App: Layout & Rasterize empty frame (Blank Screen)
```

## 2. Point of Failure
The chain breaks at **Step 4: Deserialize**. 
The response JSON from the server and the parser models on the client have misaligned contracts:
- **Public Dashboard**: The server sends widgets under `data.widgets`, while the parser reads the top-level root for `nodes`. This results in `widgets = []` inside `ConfigModel`.
- **Account / Command Center**: The server wraps the SDUI response in a `cards` sub-object, whereas the datasource passes the top-level `data` map to the `AccountHomeData.fromJson` parser which looks for `nodes` at the root. This results in `nodes = []` inside `AccountHomeData`.

Because the list of nodes evaluates to `[]`, the renderer generates a `Column` with `children: []`. The UI successfully renders a valid but empty layout, showing a blank white/dark screen underneath the App Bar.
