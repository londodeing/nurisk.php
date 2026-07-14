// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'public_database.dart';

// ignore_for_file: type=lint
class $WeatherCacheTable extends WeatherCache
    with TableInfo<$WeatherCacheTable, WeatherCacheData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $WeatherCacheTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _dataJsonMeta = const VerificationMeta(
    'dataJson',
  );
  @override
  late final GeneratedColumn<String> dataJson = GeneratedColumn<String>(
    'data_json',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _updatedAtMeta = const VerificationMeta(
    'updatedAt',
  );
  @override
  late final GeneratedColumn<DateTime> updatedAt = GeneratedColumn<DateTime>(
    'updated_at',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  @override
  List<GeneratedColumn> get $columns => [id, dataJson, updatedAt];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'weather_cache';
  @override
  VerificationContext validateIntegrity(
    Insertable<WeatherCacheData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('data_json')) {
      context.handle(
        _dataJsonMeta,
        dataJson.isAcceptableOrUnknown(data['data_json']!, _dataJsonMeta),
      );
    } else if (isInserting) {
      context.missing(_dataJsonMeta);
    }
    if (data.containsKey('updated_at')) {
      context.handle(
        _updatedAtMeta,
        updatedAt.isAcceptableOrUnknown(data['updated_at']!, _updatedAtMeta),
      );
    } else if (isInserting) {
      context.missing(_updatedAtMeta);
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  WeatherCacheData map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return WeatherCacheData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      dataJson: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}data_json'],
      )!,
      updatedAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}updated_at'],
      )!,
    );
  }

  @override
  $WeatherCacheTable createAlias(String alias) {
    return $WeatherCacheTable(attachedDatabase, alias);
  }
}

class WeatherCacheData extends DataClass
    implements Insertable<WeatherCacheData> {
  final String id;
  final String dataJson;
  final DateTime updatedAt;
  const WeatherCacheData({
    required this.id,
    required this.dataJson,
    required this.updatedAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    map['data_json'] = Variable<String>(dataJson);
    map['updated_at'] = Variable<DateTime>(updatedAt);
    return map;
  }

  WeatherCacheCompanion toCompanion(bool nullToAbsent) {
    return WeatherCacheCompanion(
      id: Value(id),
      dataJson: Value(dataJson),
      updatedAt: Value(updatedAt),
    );
  }

  factory WeatherCacheData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return WeatherCacheData(
      id: serializer.fromJson<String>(json['id']),
      dataJson: serializer.fromJson<String>(json['dataJson']),
      updatedAt: serializer.fromJson<DateTime>(json['updatedAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'dataJson': serializer.toJson<String>(dataJson),
      'updatedAt': serializer.toJson<DateTime>(updatedAt),
    };
  }

  WeatherCacheData copyWith({
    String? id,
    String? dataJson,
    DateTime? updatedAt,
  }) => WeatherCacheData(
    id: id ?? this.id,
    dataJson: dataJson ?? this.dataJson,
    updatedAt: updatedAt ?? this.updatedAt,
  );
  WeatherCacheData copyWithCompanion(WeatherCacheCompanion data) {
    return WeatherCacheData(
      id: data.id.present ? data.id.value : this.id,
      dataJson: data.dataJson.present ? data.dataJson.value : this.dataJson,
      updatedAt: data.updatedAt.present ? data.updatedAt.value : this.updatedAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('WeatherCacheData(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, dataJson, updatedAt);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is WeatherCacheData &&
          other.id == this.id &&
          other.dataJson == this.dataJson &&
          other.updatedAt == this.updatedAt);
}

class WeatherCacheCompanion extends UpdateCompanion<WeatherCacheData> {
  final Value<String> id;
  final Value<String> dataJson;
  final Value<DateTime> updatedAt;
  final Value<int> rowid;
  const WeatherCacheCompanion({
    this.id = const Value.absent(),
    this.dataJson = const Value.absent(),
    this.updatedAt = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  WeatherCacheCompanion.insert({
    required String id,
    required String dataJson,
    required DateTime updatedAt,
    this.rowid = const Value.absent(),
  }) : id = Value(id),
       dataJson = Value(dataJson),
       updatedAt = Value(updatedAt);
  static Insertable<WeatherCacheData> custom({
    Expression<String>? id,
    Expression<String>? dataJson,
    Expression<DateTime>? updatedAt,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (dataJson != null) 'data_json': dataJson,
      if (updatedAt != null) 'updated_at': updatedAt,
      if (rowid != null) 'rowid': rowid,
    });
  }

  WeatherCacheCompanion copyWith({
    Value<String>? id,
    Value<String>? dataJson,
    Value<DateTime>? updatedAt,
    Value<int>? rowid,
  }) {
    return WeatherCacheCompanion(
      id: id ?? this.id,
      dataJson: dataJson ?? this.dataJson,
      updatedAt: updatedAt ?? this.updatedAt,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (dataJson.present) {
      map['data_json'] = Variable<String>(dataJson.value);
    }
    if (updatedAt.present) {
      map['updated_at'] = Variable<DateTime>(updatedAt.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('WeatherCacheCompanion(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $IncidentCacheTable extends IncidentCache
    with TableInfo<$IncidentCacheTable, IncidentCacheData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $IncidentCacheTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _dataJsonMeta = const VerificationMeta(
    'dataJson',
  );
  @override
  late final GeneratedColumn<String> dataJson = GeneratedColumn<String>(
    'data_json',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _updatedAtMeta = const VerificationMeta(
    'updatedAt',
  );
  @override
  late final GeneratedColumn<DateTime> updatedAt = GeneratedColumn<DateTime>(
    'updated_at',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  @override
  List<GeneratedColumn> get $columns => [id, dataJson, updatedAt];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'incident_cache';
  @override
  VerificationContext validateIntegrity(
    Insertable<IncidentCacheData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('data_json')) {
      context.handle(
        _dataJsonMeta,
        dataJson.isAcceptableOrUnknown(data['data_json']!, _dataJsonMeta),
      );
    } else if (isInserting) {
      context.missing(_dataJsonMeta);
    }
    if (data.containsKey('updated_at')) {
      context.handle(
        _updatedAtMeta,
        updatedAt.isAcceptableOrUnknown(data['updated_at']!, _updatedAtMeta),
      );
    } else if (isInserting) {
      context.missing(_updatedAtMeta);
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  IncidentCacheData map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return IncidentCacheData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      dataJson: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}data_json'],
      )!,
      updatedAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}updated_at'],
      )!,
    );
  }

  @override
  $IncidentCacheTable createAlias(String alias) {
    return $IncidentCacheTable(attachedDatabase, alias);
  }
}

class IncidentCacheData extends DataClass
    implements Insertable<IncidentCacheData> {
  final String id;
  final String dataJson;
  final DateTime updatedAt;
  const IncidentCacheData({
    required this.id,
    required this.dataJson,
    required this.updatedAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    map['data_json'] = Variable<String>(dataJson);
    map['updated_at'] = Variable<DateTime>(updatedAt);
    return map;
  }

  IncidentCacheCompanion toCompanion(bool nullToAbsent) {
    return IncidentCacheCompanion(
      id: Value(id),
      dataJson: Value(dataJson),
      updatedAt: Value(updatedAt),
    );
  }

  factory IncidentCacheData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return IncidentCacheData(
      id: serializer.fromJson<String>(json['id']),
      dataJson: serializer.fromJson<String>(json['dataJson']),
      updatedAt: serializer.fromJson<DateTime>(json['updatedAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'dataJson': serializer.toJson<String>(dataJson),
      'updatedAt': serializer.toJson<DateTime>(updatedAt),
    };
  }

  IncidentCacheData copyWith({
    String? id,
    String? dataJson,
    DateTime? updatedAt,
  }) => IncidentCacheData(
    id: id ?? this.id,
    dataJson: dataJson ?? this.dataJson,
    updatedAt: updatedAt ?? this.updatedAt,
  );
  IncidentCacheData copyWithCompanion(IncidentCacheCompanion data) {
    return IncidentCacheData(
      id: data.id.present ? data.id.value : this.id,
      dataJson: data.dataJson.present ? data.dataJson.value : this.dataJson,
      updatedAt: data.updatedAt.present ? data.updatedAt.value : this.updatedAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('IncidentCacheData(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, dataJson, updatedAt);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is IncidentCacheData &&
          other.id == this.id &&
          other.dataJson == this.dataJson &&
          other.updatedAt == this.updatedAt);
}

class IncidentCacheCompanion extends UpdateCompanion<IncidentCacheData> {
  final Value<String> id;
  final Value<String> dataJson;
  final Value<DateTime> updatedAt;
  final Value<int> rowid;
  const IncidentCacheCompanion({
    this.id = const Value.absent(),
    this.dataJson = const Value.absent(),
    this.updatedAt = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  IncidentCacheCompanion.insert({
    required String id,
    required String dataJson,
    required DateTime updatedAt,
    this.rowid = const Value.absent(),
  }) : id = Value(id),
       dataJson = Value(dataJson),
       updatedAt = Value(updatedAt);
  static Insertable<IncidentCacheData> custom({
    Expression<String>? id,
    Expression<String>? dataJson,
    Expression<DateTime>? updatedAt,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (dataJson != null) 'data_json': dataJson,
      if (updatedAt != null) 'updated_at': updatedAt,
      if (rowid != null) 'rowid': rowid,
    });
  }

  IncidentCacheCompanion copyWith({
    Value<String>? id,
    Value<String>? dataJson,
    Value<DateTime>? updatedAt,
    Value<int>? rowid,
  }) {
    return IncidentCacheCompanion(
      id: id ?? this.id,
      dataJson: dataJson ?? this.dataJson,
      updatedAt: updatedAt ?? this.updatedAt,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (dataJson.present) {
      map['data_json'] = Variable<String>(dataJson.value);
    }
    if (updatedAt.present) {
      map['updated_at'] = Variable<DateTime>(updatedAt.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('IncidentCacheCompanion(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $WarningCacheTable extends WarningCache
    with TableInfo<$WarningCacheTable, WarningCacheData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $WarningCacheTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _dataJsonMeta = const VerificationMeta(
    'dataJson',
  );
  @override
  late final GeneratedColumn<String> dataJson = GeneratedColumn<String>(
    'data_json',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _updatedAtMeta = const VerificationMeta(
    'updatedAt',
  );
  @override
  late final GeneratedColumn<DateTime> updatedAt = GeneratedColumn<DateTime>(
    'updated_at',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  @override
  List<GeneratedColumn> get $columns => [id, dataJson, updatedAt];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'warning_cache';
  @override
  VerificationContext validateIntegrity(
    Insertable<WarningCacheData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('data_json')) {
      context.handle(
        _dataJsonMeta,
        dataJson.isAcceptableOrUnknown(data['data_json']!, _dataJsonMeta),
      );
    } else if (isInserting) {
      context.missing(_dataJsonMeta);
    }
    if (data.containsKey('updated_at')) {
      context.handle(
        _updatedAtMeta,
        updatedAt.isAcceptableOrUnknown(data['updated_at']!, _updatedAtMeta),
      );
    } else if (isInserting) {
      context.missing(_updatedAtMeta);
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  WarningCacheData map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return WarningCacheData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      dataJson: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}data_json'],
      )!,
      updatedAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}updated_at'],
      )!,
    );
  }

  @override
  $WarningCacheTable createAlias(String alias) {
    return $WarningCacheTable(attachedDatabase, alias);
  }
}

class WarningCacheData extends DataClass
    implements Insertable<WarningCacheData> {
  final String id;
  final String dataJson;
  final DateTime updatedAt;
  const WarningCacheData({
    required this.id,
    required this.dataJson,
    required this.updatedAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    map['data_json'] = Variable<String>(dataJson);
    map['updated_at'] = Variable<DateTime>(updatedAt);
    return map;
  }

  WarningCacheCompanion toCompanion(bool nullToAbsent) {
    return WarningCacheCompanion(
      id: Value(id),
      dataJson: Value(dataJson),
      updatedAt: Value(updatedAt),
    );
  }

  factory WarningCacheData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return WarningCacheData(
      id: serializer.fromJson<String>(json['id']),
      dataJson: serializer.fromJson<String>(json['dataJson']),
      updatedAt: serializer.fromJson<DateTime>(json['updatedAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'dataJson': serializer.toJson<String>(dataJson),
      'updatedAt': serializer.toJson<DateTime>(updatedAt),
    };
  }

  WarningCacheData copyWith({
    String? id,
    String? dataJson,
    DateTime? updatedAt,
  }) => WarningCacheData(
    id: id ?? this.id,
    dataJson: dataJson ?? this.dataJson,
    updatedAt: updatedAt ?? this.updatedAt,
  );
  WarningCacheData copyWithCompanion(WarningCacheCompanion data) {
    return WarningCacheData(
      id: data.id.present ? data.id.value : this.id,
      dataJson: data.dataJson.present ? data.dataJson.value : this.dataJson,
      updatedAt: data.updatedAt.present ? data.updatedAt.value : this.updatedAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('WarningCacheData(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, dataJson, updatedAt);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is WarningCacheData &&
          other.id == this.id &&
          other.dataJson == this.dataJson &&
          other.updatedAt == this.updatedAt);
}

class WarningCacheCompanion extends UpdateCompanion<WarningCacheData> {
  final Value<String> id;
  final Value<String> dataJson;
  final Value<DateTime> updatedAt;
  final Value<int> rowid;
  const WarningCacheCompanion({
    this.id = const Value.absent(),
    this.dataJson = const Value.absent(),
    this.updatedAt = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  WarningCacheCompanion.insert({
    required String id,
    required String dataJson,
    required DateTime updatedAt,
    this.rowid = const Value.absent(),
  }) : id = Value(id),
       dataJson = Value(dataJson),
       updatedAt = Value(updatedAt);
  static Insertable<WarningCacheData> custom({
    Expression<String>? id,
    Expression<String>? dataJson,
    Expression<DateTime>? updatedAt,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (dataJson != null) 'data_json': dataJson,
      if (updatedAt != null) 'updated_at': updatedAt,
      if (rowid != null) 'rowid': rowid,
    });
  }

  WarningCacheCompanion copyWith({
    Value<String>? id,
    Value<String>? dataJson,
    Value<DateTime>? updatedAt,
    Value<int>? rowid,
  }) {
    return WarningCacheCompanion(
      id: id ?? this.id,
      dataJson: dataJson ?? this.dataJson,
      updatedAt: updatedAt ?? this.updatedAt,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (dataJson.present) {
      map['data_json'] = Variable<String>(dataJson.value);
    }
    if (updatedAt.present) {
      map['updated_at'] = Variable<DateTime>(updatedAt.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('WarningCacheCompanion(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $DashboardKPICacheTable extends DashboardKPICache
    with TableInfo<$DashboardKPICacheTable, DashboardKPICacheData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $DashboardKPICacheTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _dataJsonMeta = const VerificationMeta(
    'dataJson',
  );
  @override
  late final GeneratedColumn<String> dataJson = GeneratedColumn<String>(
    'data_json',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _updatedAtMeta = const VerificationMeta(
    'updatedAt',
  );
  @override
  late final GeneratedColumn<DateTime> updatedAt = GeneratedColumn<DateTime>(
    'updated_at',
    aliasedName,
    false,
    type: DriftSqlType.dateTime,
    requiredDuringInsert: true,
  );
  @override
  List<GeneratedColumn> get $columns => [id, dataJson, updatedAt];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'dashboard_k_p_i_cache';
  @override
  VerificationContext validateIntegrity(
    Insertable<DashboardKPICacheData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('data_json')) {
      context.handle(
        _dataJsonMeta,
        dataJson.isAcceptableOrUnknown(data['data_json']!, _dataJsonMeta),
      );
    } else if (isInserting) {
      context.missing(_dataJsonMeta);
    }
    if (data.containsKey('updated_at')) {
      context.handle(
        _updatedAtMeta,
        updatedAt.isAcceptableOrUnknown(data['updated_at']!, _updatedAtMeta),
      );
    } else if (isInserting) {
      context.missing(_updatedAtMeta);
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  DashboardKPICacheData map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return DashboardKPICacheData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      dataJson: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}data_json'],
      )!,
      updatedAt: attachedDatabase.typeMapping.read(
        DriftSqlType.dateTime,
        data['${effectivePrefix}updated_at'],
      )!,
    );
  }

  @override
  $DashboardKPICacheTable createAlias(String alias) {
    return $DashboardKPICacheTable(attachedDatabase, alias);
  }
}

class DashboardKPICacheData extends DataClass
    implements Insertable<DashboardKPICacheData> {
  final String id;
  final String dataJson;
  final DateTime updatedAt;
  const DashboardKPICacheData({
    required this.id,
    required this.dataJson,
    required this.updatedAt,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    map['data_json'] = Variable<String>(dataJson);
    map['updated_at'] = Variable<DateTime>(updatedAt);
    return map;
  }

  DashboardKPICacheCompanion toCompanion(bool nullToAbsent) {
    return DashboardKPICacheCompanion(
      id: Value(id),
      dataJson: Value(dataJson),
      updatedAt: Value(updatedAt),
    );
  }

  factory DashboardKPICacheData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return DashboardKPICacheData(
      id: serializer.fromJson<String>(json['id']),
      dataJson: serializer.fromJson<String>(json['dataJson']),
      updatedAt: serializer.fromJson<DateTime>(json['updatedAt']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'dataJson': serializer.toJson<String>(dataJson),
      'updatedAt': serializer.toJson<DateTime>(updatedAt),
    };
  }

  DashboardKPICacheData copyWith({
    String? id,
    String? dataJson,
    DateTime? updatedAt,
  }) => DashboardKPICacheData(
    id: id ?? this.id,
    dataJson: dataJson ?? this.dataJson,
    updatedAt: updatedAt ?? this.updatedAt,
  );
  DashboardKPICacheData copyWithCompanion(DashboardKPICacheCompanion data) {
    return DashboardKPICacheData(
      id: data.id.present ? data.id.value : this.id,
      dataJson: data.dataJson.present ? data.dataJson.value : this.dataJson,
      updatedAt: data.updatedAt.present ? data.updatedAt.value : this.updatedAt,
    );
  }

  @override
  String toString() {
    return (StringBuffer('DashboardKPICacheData(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, dataJson, updatedAt);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is DashboardKPICacheData &&
          other.id == this.id &&
          other.dataJson == this.dataJson &&
          other.updatedAt == this.updatedAt);
}

class DashboardKPICacheCompanion
    extends UpdateCompanion<DashboardKPICacheData> {
  final Value<String> id;
  final Value<String> dataJson;
  final Value<DateTime> updatedAt;
  final Value<int> rowid;
  const DashboardKPICacheCompanion({
    this.id = const Value.absent(),
    this.dataJson = const Value.absent(),
    this.updatedAt = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  DashboardKPICacheCompanion.insert({
    required String id,
    required String dataJson,
    required DateTime updatedAt,
    this.rowid = const Value.absent(),
  }) : id = Value(id),
       dataJson = Value(dataJson),
       updatedAt = Value(updatedAt);
  static Insertable<DashboardKPICacheData> custom({
    Expression<String>? id,
    Expression<String>? dataJson,
    Expression<DateTime>? updatedAt,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (dataJson != null) 'data_json': dataJson,
      if (updatedAt != null) 'updated_at': updatedAt,
      if (rowid != null) 'rowid': rowid,
    });
  }

  DashboardKPICacheCompanion copyWith({
    Value<String>? id,
    Value<String>? dataJson,
    Value<DateTime>? updatedAt,
    Value<int>? rowid,
  }) {
    return DashboardKPICacheCompanion(
      id: id ?? this.id,
      dataJson: dataJson ?? this.dataJson,
      updatedAt: updatedAt ?? this.updatedAt,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (dataJson.present) {
      map['data_json'] = Variable<String>(dataJson.value);
    }
    if (updatedAt.present) {
      map['updated_at'] = Variable<DateTime>(updatedAt.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('DashboardKPICacheCompanion(')
          ..write('id: $id, ')
          ..write('dataJson: $dataJson, ')
          ..write('updatedAt: $updatedAt, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $GovernanceSuratCacheTable extends GovernanceSuratCache
    with TableInfo<$GovernanceSuratCacheTable, GovernanceSuratCacheData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $GovernanceSuratCacheTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _perihalMeta = const VerificationMeta(
    'perihal',
  );
  @override
  late final GeneratedColumn<String> perihal = GeneratedColumn<String>(
    'perihal',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _pemohonMeta = const VerificationMeta(
    'pemohon',
  );
  @override
  late final GeneratedColumn<String> pemohon = GeneratedColumn<String>(
    'pemohon',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _waktuMeta = const VerificationMeta('waktu');
  @override
  late final GeneratedColumn<String> waktu = GeneratedColumn<String>(
    'waktu',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _statusLocalMeta = const VerificationMeta(
    'statusLocal',
  );
  @override
  late final GeneratedColumn<String> statusLocal = GeneratedColumn<String>(
    'status_local',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('pending'),
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    perihal,
    pemohon,
    waktu,
    statusLocal,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'governance_surat_cache';
  @override
  VerificationContext validateIntegrity(
    Insertable<GovernanceSuratCacheData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('perihal')) {
      context.handle(
        _perihalMeta,
        perihal.isAcceptableOrUnknown(data['perihal']!, _perihalMeta),
      );
    }
    if (data.containsKey('pemohon')) {
      context.handle(
        _pemohonMeta,
        pemohon.isAcceptableOrUnknown(data['pemohon']!, _pemohonMeta),
      );
    }
    if (data.containsKey('waktu')) {
      context.handle(
        _waktuMeta,
        waktu.isAcceptableOrUnknown(data['waktu']!, _waktuMeta),
      );
    }
    if (data.containsKey('status_local')) {
      context.handle(
        _statusLocalMeta,
        statusLocal.isAcceptableOrUnknown(
          data['status_local']!,
          _statusLocalMeta,
        ),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  GovernanceSuratCacheData map(
    Map<String, dynamic> data, {
    String? tablePrefix,
  }) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return GovernanceSuratCacheData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      perihal: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}perihal'],
      ),
      pemohon: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}pemohon'],
      ),
      waktu: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}waktu'],
      ),
      statusLocal: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}status_local'],
      )!,
    );
  }

  @override
  $GovernanceSuratCacheTable createAlias(String alias) {
    return $GovernanceSuratCacheTable(attachedDatabase, alias);
  }
}

class GovernanceSuratCacheData extends DataClass
    implements Insertable<GovernanceSuratCacheData> {
  final String id;
  final String? perihal;
  final String? pemohon;
  final String? waktu;
  final String statusLocal;
  const GovernanceSuratCacheData({
    required this.id,
    this.perihal,
    this.pemohon,
    this.waktu,
    required this.statusLocal,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    if (!nullToAbsent || perihal != null) {
      map['perihal'] = Variable<String>(perihal);
    }
    if (!nullToAbsent || pemohon != null) {
      map['pemohon'] = Variable<String>(pemohon);
    }
    if (!nullToAbsent || waktu != null) {
      map['waktu'] = Variable<String>(waktu);
    }
    map['status_local'] = Variable<String>(statusLocal);
    return map;
  }

  GovernanceSuratCacheCompanion toCompanion(bool nullToAbsent) {
    return GovernanceSuratCacheCompanion(
      id: Value(id),
      perihal: perihal == null && nullToAbsent
          ? const Value.absent()
          : Value(perihal),
      pemohon: pemohon == null && nullToAbsent
          ? const Value.absent()
          : Value(pemohon),
      waktu: waktu == null && nullToAbsent
          ? const Value.absent()
          : Value(waktu),
      statusLocal: Value(statusLocal),
    );
  }

  factory GovernanceSuratCacheData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return GovernanceSuratCacheData(
      id: serializer.fromJson<String>(json['id']),
      perihal: serializer.fromJson<String?>(json['perihal']),
      pemohon: serializer.fromJson<String?>(json['pemohon']),
      waktu: serializer.fromJson<String?>(json['waktu']),
      statusLocal: serializer.fromJson<String>(json['statusLocal']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'perihal': serializer.toJson<String?>(perihal),
      'pemohon': serializer.toJson<String?>(pemohon),
      'waktu': serializer.toJson<String?>(waktu),
      'statusLocal': serializer.toJson<String>(statusLocal),
    };
  }

  GovernanceSuratCacheData copyWith({
    String? id,
    Value<String?> perihal = const Value.absent(),
    Value<String?> pemohon = const Value.absent(),
    Value<String?> waktu = const Value.absent(),
    String? statusLocal,
  }) => GovernanceSuratCacheData(
    id: id ?? this.id,
    perihal: perihal.present ? perihal.value : this.perihal,
    pemohon: pemohon.present ? pemohon.value : this.pemohon,
    waktu: waktu.present ? waktu.value : this.waktu,
    statusLocal: statusLocal ?? this.statusLocal,
  );
  GovernanceSuratCacheData copyWithCompanion(
    GovernanceSuratCacheCompanion data,
  ) {
    return GovernanceSuratCacheData(
      id: data.id.present ? data.id.value : this.id,
      perihal: data.perihal.present ? data.perihal.value : this.perihal,
      pemohon: data.pemohon.present ? data.pemohon.value : this.pemohon,
      waktu: data.waktu.present ? data.waktu.value : this.waktu,
      statusLocal: data.statusLocal.present
          ? data.statusLocal.value
          : this.statusLocal,
    );
  }

  @override
  String toString() {
    return (StringBuffer('GovernanceSuratCacheData(')
          ..write('id: $id, ')
          ..write('perihal: $perihal, ')
          ..write('pemohon: $pemohon, ')
          ..write('waktu: $waktu, ')
          ..write('statusLocal: $statusLocal')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, perihal, pemohon, waktu, statusLocal);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is GovernanceSuratCacheData &&
          other.id == this.id &&
          other.perihal == this.perihal &&
          other.pemohon == this.pemohon &&
          other.waktu == this.waktu &&
          other.statusLocal == this.statusLocal);
}

class GovernanceSuratCacheCompanion
    extends UpdateCompanion<GovernanceSuratCacheData> {
  final Value<String> id;
  final Value<String?> perihal;
  final Value<String?> pemohon;
  final Value<String?> waktu;
  final Value<String> statusLocal;
  final Value<int> rowid;
  const GovernanceSuratCacheCompanion({
    this.id = const Value.absent(),
    this.perihal = const Value.absent(),
    this.pemohon = const Value.absent(),
    this.waktu = const Value.absent(),
    this.statusLocal = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  GovernanceSuratCacheCompanion.insert({
    required String id,
    this.perihal = const Value.absent(),
    this.pemohon = const Value.absent(),
    this.waktu = const Value.absent(),
    this.statusLocal = const Value.absent(),
    this.rowid = const Value.absent(),
  }) : id = Value(id);
  static Insertable<GovernanceSuratCacheData> custom({
    Expression<String>? id,
    Expression<String>? perihal,
    Expression<String>? pemohon,
    Expression<String>? waktu,
    Expression<String>? statusLocal,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (perihal != null) 'perihal': perihal,
      if (pemohon != null) 'pemohon': pemohon,
      if (waktu != null) 'waktu': waktu,
      if (statusLocal != null) 'status_local': statusLocal,
      if (rowid != null) 'rowid': rowid,
    });
  }

  GovernanceSuratCacheCompanion copyWith({
    Value<String>? id,
    Value<String?>? perihal,
    Value<String?>? pemohon,
    Value<String?>? waktu,
    Value<String>? statusLocal,
    Value<int>? rowid,
  }) {
    return GovernanceSuratCacheCompanion(
      id: id ?? this.id,
      perihal: perihal ?? this.perihal,
      pemohon: pemohon ?? this.pemohon,
      waktu: waktu ?? this.waktu,
      statusLocal: statusLocal ?? this.statusLocal,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (perihal.present) {
      map['perihal'] = Variable<String>(perihal.value);
    }
    if (pemohon.present) {
      map['pemohon'] = Variable<String>(pemohon.value);
    }
    if (waktu.present) {
      map['waktu'] = Variable<String>(waktu.value);
    }
    if (statusLocal.present) {
      map['status_local'] = Variable<String>(statusLocal.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('GovernanceSuratCacheCompanion(')
          ..write('id: $id, ')
          ..write('perihal: $perihal, ')
          ..write('pemohon: $pemohon, ')
          ..write('waktu: $waktu, ')
          ..write('statusLocal: $statusLocal, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

class $GovernancePlenoCacheTable extends GovernancePlenoCache
    with TableInfo<$GovernancePlenoCacheTable, GovernancePlenoCacheData> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $GovernancePlenoCacheTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<String> id = GeneratedColumn<String>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _judulMeta = const VerificationMeta('judul');
  @override
  late final GeneratedColumn<String> judul = GeneratedColumn<String>(
    'judul',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _insidenMeta = const VerificationMeta(
    'insiden',
  );
  @override
  late final GeneratedColumn<String> insiden = GeneratedColumn<String>(
    'insiden',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _waktuMeta = const VerificationMeta('waktu');
  @override
  late final GeneratedColumn<String> waktu = GeneratedColumn<String>(
    'waktu',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _statusLocalMeta = const VerificationMeta(
    'statusLocal',
  );
  @override
  late final GeneratedColumn<String> statusLocal = GeneratedColumn<String>(
    'status_local',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
    defaultValue: const Constant('pending'),
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    judul,
    insiden,
    waktu,
    statusLocal,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'governance_pleno_cache';
  @override
  VerificationContext validateIntegrity(
    Insertable<GovernancePlenoCacheData> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    } else if (isInserting) {
      context.missing(_idMeta);
    }
    if (data.containsKey('judul')) {
      context.handle(
        _judulMeta,
        judul.isAcceptableOrUnknown(data['judul']!, _judulMeta),
      );
    }
    if (data.containsKey('insiden')) {
      context.handle(
        _insidenMeta,
        insiden.isAcceptableOrUnknown(data['insiden']!, _insidenMeta),
      );
    }
    if (data.containsKey('waktu')) {
      context.handle(
        _waktuMeta,
        waktu.isAcceptableOrUnknown(data['waktu']!, _waktuMeta),
      );
    }
    if (data.containsKey('status_local')) {
      context.handle(
        _statusLocalMeta,
        statusLocal.isAcceptableOrUnknown(
          data['status_local']!,
          _statusLocalMeta,
        ),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  GovernancePlenoCacheData map(
    Map<String, dynamic> data, {
    String? tablePrefix,
  }) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return GovernancePlenoCacheData(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}id'],
      )!,
      judul: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}judul'],
      ),
      insiden: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}insiden'],
      ),
      waktu: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}waktu'],
      ),
      statusLocal: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}status_local'],
      )!,
    );
  }

  @override
  $GovernancePlenoCacheTable createAlias(String alias) {
    return $GovernancePlenoCacheTable(attachedDatabase, alias);
  }
}

class GovernancePlenoCacheData extends DataClass
    implements Insertable<GovernancePlenoCacheData> {
  final String id;
  final String? judul;
  final String? insiden;
  final String? waktu;
  final String statusLocal;
  const GovernancePlenoCacheData({
    required this.id,
    this.judul,
    this.insiden,
    this.waktu,
    required this.statusLocal,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<String>(id);
    if (!nullToAbsent || judul != null) {
      map['judul'] = Variable<String>(judul);
    }
    if (!nullToAbsent || insiden != null) {
      map['insiden'] = Variable<String>(insiden);
    }
    if (!nullToAbsent || waktu != null) {
      map['waktu'] = Variable<String>(waktu);
    }
    map['status_local'] = Variable<String>(statusLocal);
    return map;
  }

  GovernancePlenoCacheCompanion toCompanion(bool nullToAbsent) {
    return GovernancePlenoCacheCompanion(
      id: Value(id),
      judul: judul == null && nullToAbsent
          ? const Value.absent()
          : Value(judul),
      insiden: insiden == null && nullToAbsent
          ? const Value.absent()
          : Value(insiden),
      waktu: waktu == null && nullToAbsent
          ? const Value.absent()
          : Value(waktu),
      statusLocal: Value(statusLocal),
    );
  }

  factory GovernancePlenoCacheData.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return GovernancePlenoCacheData(
      id: serializer.fromJson<String>(json['id']),
      judul: serializer.fromJson<String?>(json['judul']),
      insiden: serializer.fromJson<String?>(json['insiden']),
      waktu: serializer.fromJson<String?>(json['waktu']),
      statusLocal: serializer.fromJson<String>(json['statusLocal']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<String>(id),
      'judul': serializer.toJson<String?>(judul),
      'insiden': serializer.toJson<String?>(insiden),
      'waktu': serializer.toJson<String?>(waktu),
      'statusLocal': serializer.toJson<String>(statusLocal),
    };
  }

  GovernancePlenoCacheData copyWith({
    String? id,
    Value<String?> judul = const Value.absent(),
    Value<String?> insiden = const Value.absent(),
    Value<String?> waktu = const Value.absent(),
    String? statusLocal,
  }) => GovernancePlenoCacheData(
    id: id ?? this.id,
    judul: judul.present ? judul.value : this.judul,
    insiden: insiden.present ? insiden.value : this.insiden,
    waktu: waktu.present ? waktu.value : this.waktu,
    statusLocal: statusLocal ?? this.statusLocal,
  );
  GovernancePlenoCacheData copyWithCompanion(
    GovernancePlenoCacheCompanion data,
  ) {
    return GovernancePlenoCacheData(
      id: data.id.present ? data.id.value : this.id,
      judul: data.judul.present ? data.judul.value : this.judul,
      insiden: data.insiden.present ? data.insiden.value : this.insiden,
      waktu: data.waktu.present ? data.waktu.value : this.waktu,
      statusLocal: data.statusLocal.present
          ? data.statusLocal.value
          : this.statusLocal,
    );
  }

  @override
  String toString() {
    return (StringBuffer('GovernancePlenoCacheData(')
          ..write('id: $id, ')
          ..write('judul: $judul, ')
          ..write('insiden: $insiden, ')
          ..write('waktu: $waktu, ')
          ..write('statusLocal: $statusLocal')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(id, judul, insiden, waktu, statusLocal);
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is GovernancePlenoCacheData &&
          other.id == this.id &&
          other.judul == this.judul &&
          other.insiden == this.insiden &&
          other.waktu == this.waktu &&
          other.statusLocal == this.statusLocal);
}

class GovernancePlenoCacheCompanion
    extends UpdateCompanion<GovernancePlenoCacheData> {
  final Value<String> id;
  final Value<String?> judul;
  final Value<String?> insiden;
  final Value<String?> waktu;
  final Value<String> statusLocal;
  final Value<int> rowid;
  const GovernancePlenoCacheCompanion({
    this.id = const Value.absent(),
    this.judul = const Value.absent(),
    this.insiden = const Value.absent(),
    this.waktu = const Value.absent(),
    this.statusLocal = const Value.absent(),
    this.rowid = const Value.absent(),
  });
  GovernancePlenoCacheCompanion.insert({
    required String id,
    this.judul = const Value.absent(),
    this.insiden = const Value.absent(),
    this.waktu = const Value.absent(),
    this.statusLocal = const Value.absent(),
    this.rowid = const Value.absent(),
  }) : id = Value(id);
  static Insertable<GovernancePlenoCacheData> custom({
    Expression<String>? id,
    Expression<String>? judul,
    Expression<String>? insiden,
    Expression<String>? waktu,
    Expression<String>? statusLocal,
    Expression<int>? rowid,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (judul != null) 'judul': judul,
      if (insiden != null) 'insiden': insiden,
      if (waktu != null) 'waktu': waktu,
      if (statusLocal != null) 'status_local': statusLocal,
      if (rowid != null) 'rowid': rowid,
    });
  }

  GovernancePlenoCacheCompanion copyWith({
    Value<String>? id,
    Value<String?>? judul,
    Value<String?>? insiden,
    Value<String?>? waktu,
    Value<String>? statusLocal,
    Value<int>? rowid,
  }) {
    return GovernancePlenoCacheCompanion(
      id: id ?? this.id,
      judul: judul ?? this.judul,
      insiden: insiden ?? this.insiden,
      waktu: waktu ?? this.waktu,
      statusLocal: statusLocal ?? this.statusLocal,
      rowid: rowid ?? this.rowid,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<String>(id.value);
    }
    if (judul.present) {
      map['judul'] = Variable<String>(judul.value);
    }
    if (insiden.present) {
      map['insiden'] = Variable<String>(insiden.value);
    }
    if (waktu.present) {
      map['waktu'] = Variable<String>(waktu.value);
    }
    if (statusLocal.present) {
      map['status_local'] = Variable<String>(statusLocal.value);
    }
    if (rowid.present) {
      map['rowid'] = Variable<int>(rowid.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('GovernancePlenoCacheCompanion(')
          ..write('id: $id, ')
          ..write('judul: $judul, ')
          ..write('insiden: $insiden, ')
          ..write('waktu: $waktu, ')
          ..write('statusLocal: $statusLocal, ')
          ..write('rowid: $rowid')
          ..write(')'))
        .toString();
  }
}

abstract class _$PublicDatabase extends GeneratedDatabase {
  _$PublicDatabase(QueryExecutor e) : super(e);
  $PublicDatabaseManager get managers => $PublicDatabaseManager(this);
  late final $WeatherCacheTable weatherCache = $WeatherCacheTable(this);
  late final $IncidentCacheTable incidentCache = $IncidentCacheTable(this);
  late final $WarningCacheTable warningCache = $WarningCacheTable(this);
  late final $DashboardKPICacheTable dashboardKPICache =
      $DashboardKPICacheTable(this);
  late final $GovernanceSuratCacheTable governanceSuratCache =
      $GovernanceSuratCacheTable(this);
  late final $GovernancePlenoCacheTable governancePlenoCache =
      $GovernancePlenoCacheTable(this);
  @override
  Iterable<TableInfo<Table, Object?>> get allTables =>
      allSchemaEntities.whereType<TableInfo<Table, Object?>>();
  @override
  List<DatabaseSchemaEntity> get allSchemaEntities => [
    weatherCache,
    incidentCache,
    warningCache,
    dashboardKPICache,
    governanceSuratCache,
    governancePlenoCache,
  ];
}

typedef $$WeatherCacheTableCreateCompanionBuilder =
    WeatherCacheCompanion Function({
      required String id,
      required String dataJson,
      required DateTime updatedAt,
      Value<int> rowid,
    });
typedef $$WeatherCacheTableUpdateCompanionBuilder =
    WeatherCacheCompanion Function({
      Value<String> id,
      Value<String> dataJson,
      Value<DateTime> updatedAt,
      Value<int> rowid,
    });

class $$WeatherCacheTableFilterComposer
    extends Composer<_$PublicDatabase, $WeatherCacheTable> {
  $$WeatherCacheTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$WeatherCacheTableOrderingComposer
    extends Composer<_$PublicDatabase, $WeatherCacheTable> {
  $$WeatherCacheTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$WeatherCacheTableAnnotationComposer
    extends Composer<_$PublicDatabase, $WeatherCacheTable> {
  $$WeatherCacheTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get dataJson =>
      $composableBuilder(column: $table.dataJson, builder: (column) => column);

  GeneratedColumn<DateTime> get updatedAt =>
      $composableBuilder(column: $table.updatedAt, builder: (column) => column);
}

class $$WeatherCacheTableTableManager
    extends
        RootTableManager<
          _$PublicDatabase,
          $WeatherCacheTable,
          WeatherCacheData,
          $$WeatherCacheTableFilterComposer,
          $$WeatherCacheTableOrderingComposer,
          $$WeatherCacheTableAnnotationComposer,
          $$WeatherCacheTableCreateCompanionBuilder,
          $$WeatherCacheTableUpdateCompanionBuilder,
          (
            WeatherCacheData,
            BaseReferences<
              _$PublicDatabase,
              $WeatherCacheTable,
              WeatherCacheData
            >,
          ),
          WeatherCacheData,
          PrefetchHooks Function()
        > {
  $$WeatherCacheTableTableManager(_$PublicDatabase db, $WeatherCacheTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$WeatherCacheTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$WeatherCacheTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$WeatherCacheTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String> dataJson = const Value.absent(),
                Value<DateTime> updatedAt = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => WeatherCacheCompanion(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                required String dataJson,
                required DateTime updatedAt,
                Value<int> rowid = const Value.absent(),
              }) => WeatherCacheCompanion.insert(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$WeatherCacheTableProcessedTableManager =
    ProcessedTableManager<
      _$PublicDatabase,
      $WeatherCacheTable,
      WeatherCacheData,
      $$WeatherCacheTableFilterComposer,
      $$WeatherCacheTableOrderingComposer,
      $$WeatherCacheTableAnnotationComposer,
      $$WeatherCacheTableCreateCompanionBuilder,
      $$WeatherCacheTableUpdateCompanionBuilder,
      (
        WeatherCacheData,
        BaseReferences<_$PublicDatabase, $WeatherCacheTable, WeatherCacheData>,
      ),
      WeatherCacheData,
      PrefetchHooks Function()
    >;
typedef $$IncidentCacheTableCreateCompanionBuilder =
    IncidentCacheCompanion Function({
      required String id,
      required String dataJson,
      required DateTime updatedAt,
      Value<int> rowid,
    });
typedef $$IncidentCacheTableUpdateCompanionBuilder =
    IncidentCacheCompanion Function({
      Value<String> id,
      Value<String> dataJson,
      Value<DateTime> updatedAt,
      Value<int> rowid,
    });

class $$IncidentCacheTableFilterComposer
    extends Composer<_$PublicDatabase, $IncidentCacheTable> {
  $$IncidentCacheTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$IncidentCacheTableOrderingComposer
    extends Composer<_$PublicDatabase, $IncidentCacheTable> {
  $$IncidentCacheTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$IncidentCacheTableAnnotationComposer
    extends Composer<_$PublicDatabase, $IncidentCacheTable> {
  $$IncidentCacheTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get dataJson =>
      $composableBuilder(column: $table.dataJson, builder: (column) => column);

  GeneratedColumn<DateTime> get updatedAt =>
      $composableBuilder(column: $table.updatedAt, builder: (column) => column);
}

class $$IncidentCacheTableTableManager
    extends
        RootTableManager<
          _$PublicDatabase,
          $IncidentCacheTable,
          IncidentCacheData,
          $$IncidentCacheTableFilterComposer,
          $$IncidentCacheTableOrderingComposer,
          $$IncidentCacheTableAnnotationComposer,
          $$IncidentCacheTableCreateCompanionBuilder,
          $$IncidentCacheTableUpdateCompanionBuilder,
          (
            IncidentCacheData,
            BaseReferences<
              _$PublicDatabase,
              $IncidentCacheTable,
              IncidentCacheData
            >,
          ),
          IncidentCacheData,
          PrefetchHooks Function()
        > {
  $$IncidentCacheTableTableManager(
    _$PublicDatabase db,
    $IncidentCacheTable table,
  ) : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$IncidentCacheTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$IncidentCacheTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$IncidentCacheTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String> dataJson = const Value.absent(),
                Value<DateTime> updatedAt = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => IncidentCacheCompanion(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                required String dataJson,
                required DateTime updatedAt,
                Value<int> rowid = const Value.absent(),
              }) => IncidentCacheCompanion.insert(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$IncidentCacheTableProcessedTableManager =
    ProcessedTableManager<
      _$PublicDatabase,
      $IncidentCacheTable,
      IncidentCacheData,
      $$IncidentCacheTableFilterComposer,
      $$IncidentCacheTableOrderingComposer,
      $$IncidentCacheTableAnnotationComposer,
      $$IncidentCacheTableCreateCompanionBuilder,
      $$IncidentCacheTableUpdateCompanionBuilder,
      (
        IncidentCacheData,
        BaseReferences<
          _$PublicDatabase,
          $IncidentCacheTable,
          IncidentCacheData
        >,
      ),
      IncidentCacheData,
      PrefetchHooks Function()
    >;
typedef $$WarningCacheTableCreateCompanionBuilder =
    WarningCacheCompanion Function({
      required String id,
      required String dataJson,
      required DateTime updatedAt,
      Value<int> rowid,
    });
typedef $$WarningCacheTableUpdateCompanionBuilder =
    WarningCacheCompanion Function({
      Value<String> id,
      Value<String> dataJson,
      Value<DateTime> updatedAt,
      Value<int> rowid,
    });

class $$WarningCacheTableFilterComposer
    extends Composer<_$PublicDatabase, $WarningCacheTable> {
  $$WarningCacheTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$WarningCacheTableOrderingComposer
    extends Composer<_$PublicDatabase, $WarningCacheTable> {
  $$WarningCacheTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$WarningCacheTableAnnotationComposer
    extends Composer<_$PublicDatabase, $WarningCacheTable> {
  $$WarningCacheTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get dataJson =>
      $composableBuilder(column: $table.dataJson, builder: (column) => column);

  GeneratedColumn<DateTime> get updatedAt =>
      $composableBuilder(column: $table.updatedAt, builder: (column) => column);
}

class $$WarningCacheTableTableManager
    extends
        RootTableManager<
          _$PublicDatabase,
          $WarningCacheTable,
          WarningCacheData,
          $$WarningCacheTableFilterComposer,
          $$WarningCacheTableOrderingComposer,
          $$WarningCacheTableAnnotationComposer,
          $$WarningCacheTableCreateCompanionBuilder,
          $$WarningCacheTableUpdateCompanionBuilder,
          (
            WarningCacheData,
            BaseReferences<
              _$PublicDatabase,
              $WarningCacheTable,
              WarningCacheData
            >,
          ),
          WarningCacheData,
          PrefetchHooks Function()
        > {
  $$WarningCacheTableTableManager(_$PublicDatabase db, $WarningCacheTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$WarningCacheTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$WarningCacheTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$WarningCacheTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String> dataJson = const Value.absent(),
                Value<DateTime> updatedAt = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => WarningCacheCompanion(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                required String dataJson,
                required DateTime updatedAt,
                Value<int> rowid = const Value.absent(),
              }) => WarningCacheCompanion.insert(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$WarningCacheTableProcessedTableManager =
    ProcessedTableManager<
      _$PublicDatabase,
      $WarningCacheTable,
      WarningCacheData,
      $$WarningCacheTableFilterComposer,
      $$WarningCacheTableOrderingComposer,
      $$WarningCacheTableAnnotationComposer,
      $$WarningCacheTableCreateCompanionBuilder,
      $$WarningCacheTableUpdateCompanionBuilder,
      (
        WarningCacheData,
        BaseReferences<_$PublicDatabase, $WarningCacheTable, WarningCacheData>,
      ),
      WarningCacheData,
      PrefetchHooks Function()
    >;
typedef $$DashboardKPICacheTableCreateCompanionBuilder =
    DashboardKPICacheCompanion Function({
      required String id,
      required String dataJson,
      required DateTime updatedAt,
      Value<int> rowid,
    });
typedef $$DashboardKPICacheTableUpdateCompanionBuilder =
    DashboardKPICacheCompanion Function({
      Value<String> id,
      Value<String> dataJson,
      Value<DateTime> updatedAt,
      Value<int> rowid,
    });

class $$DashboardKPICacheTableFilterComposer
    extends Composer<_$PublicDatabase, $DashboardKPICacheTable> {
  $$DashboardKPICacheTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnFilters(column),
  );
}

class $$DashboardKPICacheTableOrderingComposer
    extends Composer<_$PublicDatabase, $DashboardKPICacheTable> {
  $$DashboardKPICacheTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get dataJson => $composableBuilder(
    column: $table.dataJson,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<DateTime> get updatedAt => $composableBuilder(
    column: $table.updatedAt,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$DashboardKPICacheTableAnnotationComposer
    extends Composer<_$PublicDatabase, $DashboardKPICacheTable> {
  $$DashboardKPICacheTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get dataJson =>
      $composableBuilder(column: $table.dataJson, builder: (column) => column);

  GeneratedColumn<DateTime> get updatedAt =>
      $composableBuilder(column: $table.updatedAt, builder: (column) => column);
}

class $$DashboardKPICacheTableTableManager
    extends
        RootTableManager<
          _$PublicDatabase,
          $DashboardKPICacheTable,
          DashboardKPICacheData,
          $$DashboardKPICacheTableFilterComposer,
          $$DashboardKPICacheTableOrderingComposer,
          $$DashboardKPICacheTableAnnotationComposer,
          $$DashboardKPICacheTableCreateCompanionBuilder,
          $$DashboardKPICacheTableUpdateCompanionBuilder,
          (
            DashboardKPICacheData,
            BaseReferences<
              _$PublicDatabase,
              $DashboardKPICacheTable,
              DashboardKPICacheData
            >,
          ),
          DashboardKPICacheData,
          PrefetchHooks Function()
        > {
  $$DashboardKPICacheTableTableManager(
    _$PublicDatabase db,
    $DashboardKPICacheTable table,
  ) : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$DashboardKPICacheTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$DashboardKPICacheTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$DashboardKPICacheTableAnnotationComposer(
                $db: db,
                $table: table,
              ),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String> dataJson = const Value.absent(),
                Value<DateTime> updatedAt = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => DashboardKPICacheCompanion(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                required String dataJson,
                required DateTime updatedAt,
                Value<int> rowid = const Value.absent(),
              }) => DashboardKPICacheCompanion.insert(
                id: id,
                dataJson: dataJson,
                updatedAt: updatedAt,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$DashboardKPICacheTableProcessedTableManager =
    ProcessedTableManager<
      _$PublicDatabase,
      $DashboardKPICacheTable,
      DashboardKPICacheData,
      $$DashboardKPICacheTableFilterComposer,
      $$DashboardKPICacheTableOrderingComposer,
      $$DashboardKPICacheTableAnnotationComposer,
      $$DashboardKPICacheTableCreateCompanionBuilder,
      $$DashboardKPICacheTableUpdateCompanionBuilder,
      (
        DashboardKPICacheData,
        BaseReferences<
          _$PublicDatabase,
          $DashboardKPICacheTable,
          DashboardKPICacheData
        >,
      ),
      DashboardKPICacheData,
      PrefetchHooks Function()
    >;
typedef $$GovernanceSuratCacheTableCreateCompanionBuilder =
    GovernanceSuratCacheCompanion Function({
      required String id,
      Value<String?> perihal,
      Value<String?> pemohon,
      Value<String?> waktu,
      Value<String> statusLocal,
      Value<int> rowid,
    });
typedef $$GovernanceSuratCacheTableUpdateCompanionBuilder =
    GovernanceSuratCacheCompanion Function({
      Value<String> id,
      Value<String?> perihal,
      Value<String?> pemohon,
      Value<String?> waktu,
      Value<String> statusLocal,
      Value<int> rowid,
    });

class $$GovernanceSuratCacheTableFilterComposer
    extends Composer<_$PublicDatabase, $GovernanceSuratCacheTable> {
  $$GovernanceSuratCacheTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get perihal => $composableBuilder(
    column: $table.perihal,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get pemohon => $composableBuilder(
    column: $table.pemohon,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get waktu => $composableBuilder(
    column: $table.waktu,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get statusLocal => $composableBuilder(
    column: $table.statusLocal,
    builder: (column) => ColumnFilters(column),
  );
}

class $$GovernanceSuratCacheTableOrderingComposer
    extends Composer<_$PublicDatabase, $GovernanceSuratCacheTable> {
  $$GovernanceSuratCacheTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get perihal => $composableBuilder(
    column: $table.perihal,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get pemohon => $composableBuilder(
    column: $table.pemohon,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get waktu => $composableBuilder(
    column: $table.waktu,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get statusLocal => $composableBuilder(
    column: $table.statusLocal,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$GovernanceSuratCacheTableAnnotationComposer
    extends Composer<_$PublicDatabase, $GovernanceSuratCacheTable> {
  $$GovernanceSuratCacheTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get perihal =>
      $composableBuilder(column: $table.perihal, builder: (column) => column);

  GeneratedColumn<String> get pemohon =>
      $composableBuilder(column: $table.pemohon, builder: (column) => column);

  GeneratedColumn<String> get waktu =>
      $composableBuilder(column: $table.waktu, builder: (column) => column);

  GeneratedColumn<String> get statusLocal => $composableBuilder(
    column: $table.statusLocal,
    builder: (column) => column,
  );
}

class $$GovernanceSuratCacheTableTableManager
    extends
        RootTableManager<
          _$PublicDatabase,
          $GovernanceSuratCacheTable,
          GovernanceSuratCacheData,
          $$GovernanceSuratCacheTableFilterComposer,
          $$GovernanceSuratCacheTableOrderingComposer,
          $$GovernanceSuratCacheTableAnnotationComposer,
          $$GovernanceSuratCacheTableCreateCompanionBuilder,
          $$GovernanceSuratCacheTableUpdateCompanionBuilder,
          (
            GovernanceSuratCacheData,
            BaseReferences<
              _$PublicDatabase,
              $GovernanceSuratCacheTable,
              GovernanceSuratCacheData
            >,
          ),
          GovernanceSuratCacheData,
          PrefetchHooks Function()
        > {
  $$GovernanceSuratCacheTableTableManager(
    _$PublicDatabase db,
    $GovernanceSuratCacheTable table,
  ) : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$GovernanceSuratCacheTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$GovernanceSuratCacheTableOrderingComposer(
                $db: db,
                $table: table,
              ),
          createComputedFieldComposer: () =>
              $$GovernanceSuratCacheTableAnnotationComposer(
                $db: db,
                $table: table,
              ),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String?> perihal = const Value.absent(),
                Value<String?> pemohon = const Value.absent(),
                Value<String?> waktu = const Value.absent(),
                Value<String> statusLocal = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => GovernanceSuratCacheCompanion(
                id: id,
                perihal: perihal,
                pemohon: pemohon,
                waktu: waktu,
                statusLocal: statusLocal,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                Value<String?> perihal = const Value.absent(),
                Value<String?> pemohon = const Value.absent(),
                Value<String?> waktu = const Value.absent(),
                Value<String> statusLocal = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => GovernanceSuratCacheCompanion.insert(
                id: id,
                perihal: perihal,
                pemohon: pemohon,
                waktu: waktu,
                statusLocal: statusLocal,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$GovernanceSuratCacheTableProcessedTableManager =
    ProcessedTableManager<
      _$PublicDatabase,
      $GovernanceSuratCacheTable,
      GovernanceSuratCacheData,
      $$GovernanceSuratCacheTableFilterComposer,
      $$GovernanceSuratCacheTableOrderingComposer,
      $$GovernanceSuratCacheTableAnnotationComposer,
      $$GovernanceSuratCacheTableCreateCompanionBuilder,
      $$GovernanceSuratCacheTableUpdateCompanionBuilder,
      (
        GovernanceSuratCacheData,
        BaseReferences<
          _$PublicDatabase,
          $GovernanceSuratCacheTable,
          GovernanceSuratCacheData
        >,
      ),
      GovernanceSuratCacheData,
      PrefetchHooks Function()
    >;
typedef $$GovernancePlenoCacheTableCreateCompanionBuilder =
    GovernancePlenoCacheCompanion Function({
      required String id,
      Value<String?> judul,
      Value<String?> insiden,
      Value<String?> waktu,
      Value<String> statusLocal,
      Value<int> rowid,
    });
typedef $$GovernancePlenoCacheTableUpdateCompanionBuilder =
    GovernancePlenoCacheCompanion Function({
      Value<String> id,
      Value<String?> judul,
      Value<String?> insiden,
      Value<String?> waktu,
      Value<String> statusLocal,
      Value<int> rowid,
    });

class $$GovernancePlenoCacheTableFilterComposer
    extends Composer<_$PublicDatabase, $GovernancePlenoCacheTable> {
  $$GovernancePlenoCacheTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get judul => $composableBuilder(
    column: $table.judul,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get insiden => $composableBuilder(
    column: $table.insiden,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get waktu => $composableBuilder(
    column: $table.waktu,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get statusLocal => $composableBuilder(
    column: $table.statusLocal,
    builder: (column) => ColumnFilters(column),
  );
}

class $$GovernancePlenoCacheTableOrderingComposer
    extends Composer<_$PublicDatabase, $GovernancePlenoCacheTable> {
  $$GovernancePlenoCacheTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<String> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get judul => $composableBuilder(
    column: $table.judul,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get insiden => $composableBuilder(
    column: $table.insiden,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get waktu => $composableBuilder(
    column: $table.waktu,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get statusLocal => $composableBuilder(
    column: $table.statusLocal,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$GovernancePlenoCacheTableAnnotationComposer
    extends Composer<_$PublicDatabase, $GovernancePlenoCacheTable> {
  $$GovernancePlenoCacheTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<String> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get judul =>
      $composableBuilder(column: $table.judul, builder: (column) => column);

  GeneratedColumn<String> get insiden =>
      $composableBuilder(column: $table.insiden, builder: (column) => column);

  GeneratedColumn<String> get waktu =>
      $composableBuilder(column: $table.waktu, builder: (column) => column);

  GeneratedColumn<String> get statusLocal => $composableBuilder(
    column: $table.statusLocal,
    builder: (column) => column,
  );
}

class $$GovernancePlenoCacheTableTableManager
    extends
        RootTableManager<
          _$PublicDatabase,
          $GovernancePlenoCacheTable,
          GovernancePlenoCacheData,
          $$GovernancePlenoCacheTableFilterComposer,
          $$GovernancePlenoCacheTableOrderingComposer,
          $$GovernancePlenoCacheTableAnnotationComposer,
          $$GovernancePlenoCacheTableCreateCompanionBuilder,
          $$GovernancePlenoCacheTableUpdateCompanionBuilder,
          (
            GovernancePlenoCacheData,
            BaseReferences<
              _$PublicDatabase,
              $GovernancePlenoCacheTable,
              GovernancePlenoCacheData
            >,
          ),
          GovernancePlenoCacheData,
          PrefetchHooks Function()
        > {
  $$GovernancePlenoCacheTableTableManager(
    _$PublicDatabase db,
    $GovernancePlenoCacheTable table,
  ) : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$GovernancePlenoCacheTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$GovernancePlenoCacheTableOrderingComposer(
                $db: db,
                $table: table,
              ),
          createComputedFieldComposer: () =>
              $$GovernancePlenoCacheTableAnnotationComposer(
                $db: db,
                $table: table,
              ),
          updateCompanionCallback:
              ({
                Value<String> id = const Value.absent(),
                Value<String?> judul = const Value.absent(),
                Value<String?> insiden = const Value.absent(),
                Value<String?> waktu = const Value.absent(),
                Value<String> statusLocal = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => GovernancePlenoCacheCompanion(
                id: id,
                judul: judul,
                insiden: insiden,
                waktu: waktu,
                statusLocal: statusLocal,
                rowid: rowid,
              ),
          createCompanionCallback:
              ({
                required String id,
                Value<String?> judul = const Value.absent(),
                Value<String?> insiden = const Value.absent(),
                Value<String?> waktu = const Value.absent(),
                Value<String> statusLocal = const Value.absent(),
                Value<int> rowid = const Value.absent(),
              }) => GovernancePlenoCacheCompanion.insert(
                id: id,
                judul: judul,
                insiden: insiden,
                waktu: waktu,
                statusLocal: statusLocal,
                rowid: rowid,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$GovernancePlenoCacheTableProcessedTableManager =
    ProcessedTableManager<
      _$PublicDatabase,
      $GovernancePlenoCacheTable,
      GovernancePlenoCacheData,
      $$GovernancePlenoCacheTableFilterComposer,
      $$GovernancePlenoCacheTableOrderingComposer,
      $$GovernancePlenoCacheTableAnnotationComposer,
      $$GovernancePlenoCacheTableCreateCompanionBuilder,
      $$GovernancePlenoCacheTableUpdateCompanionBuilder,
      (
        GovernancePlenoCacheData,
        BaseReferences<
          _$PublicDatabase,
          $GovernancePlenoCacheTable,
          GovernancePlenoCacheData
        >,
      ),
      GovernancePlenoCacheData,
      PrefetchHooks Function()
    >;

class $PublicDatabaseManager {
  final _$PublicDatabase _db;
  $PublicDatabaseManager(this._db);
  $$WeatherCacheTableTableManager get weatherCache =>
      $$WeatherCacheTableTableManager(_db, _db.weatherCache);
  $$IncidentCacheTableTableManager get incidentCache =>
      $$IncidentCacheTableTableManager(_db, _db.incidentCache);
  $$WarningCacheTableTableManager get warningCache =>
      $$WarningCacheTableTableManager(_db, _db.warningCache);
  $$DashboardKPICacheTableTableManager get dashboardKPICache =>
      $$DashboardKPICacheTableTableManager(_db, _db.dashboardKPICache);
  $$GovernanceSuratCacheTableTableManager get governanceSuratCache =>
      $$GovernanceSuratCacheTableTableManager(_db, _db.governanceSuratCache);
  $$GovernancePlenoCacheTableTableManager get governancePlenoCache =>
      $$GovernancePlenoCacheTableTableManager(_db, _db.governancePlenoCache);
}
