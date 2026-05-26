import mongoose from 'mongoose';

const defaultMongoURI = 'mongodb://localhost:27017/sci_literacy';

function castToObjectId(obj) {
  if (obj === null || obj === undefined) return obj;
  if (typeof obj === 'object') {
    if (Array.isArray(obj)) {
      for (let i = 0; i < obj.length; i++) {
        if (typeof obj[i] === 'string' && /^[0-9a-fA-F]{24}$/.test(obj[i])) {
          try {
            obj[i] = new mongoose.Types.ObjectId(obj[i]);
          } catch (e) {}
        } else if (typeof obj[i] === 'object') {
          obj[i] = castToObjectId(obj[i]);
        }
      }
    } else {
      for (const key in obj) {
        const val = obj[key];
        if (typeof val === 'string' && /^[0-9a-fA-F]{24}$/.test(val) && (key === '_id' || key.endsWith('_id'))) {
          try {
            obj[key] = new mongoose.Types.ObjectId(val);
          } catch (e) {}
        } else if (Array.isArray(val) && (key === '_id' || key.endsWith('_id') || key === '$in' || key === '$nin')) {
          obj[key] = val.map(v => {
            if (typeof v === 'string' && /^[0-9a-fA-F]{24}$/.test(v)) {
              try {
                return new mongoose.Types.ObjectId(v);
              } catch (e) {
                return v;
              }
            }
            return castToObjectId(v);
          });
        } else if (typeof val === 'object') {
          obj[key] = castToObjectId(val);
        }
      }
    }
  }
  return obj;
}

async function main() {
  const args = process.argv.slice(2);
  if (args.length === 0) {
    console.log(JSON.stringify({ success: false, error: 'No arguments provided' }));
    process.exit(1);
  }

  let payload;
  try {
    const base64Data = args[0];
    const decodedString = Buffer.from(base64Data, 'base64').toString('utf8');
    payload = JSON.parse(decodedString);
  } catch (e) {
    console.log(JSON.stringify({ success: false, error: 'Invalid Base64 or JSON payload: ' + e.message }));
    process.exit(1);
  }

  let { action, collection, filter, data, sort, limit, uri } = payload;
  const connectionURI = uri || defaultMongoURI;

  // Normalize filter
  if (!filter || Array.isArray(filter) || typeof filter !== 'object') {
    filter = {};
  } else {
    filter = castToObjectId(filter);
  }

  // Normalize data
  if (data && typeof data === 'object') {
    data = castToObjectId(data);
  }

  try {
    await mongoose.connect(connectionURI, { serverSelectionTimeoutMS: 5000 });
    
    const db = mongoose.connection.db;
    const col = db.collection(collection);

    let result;
    if (action === 'insert') {
      result = await col.insertOne(data);
    } else if (action === 'find') {
      let query = col.find(filter);
      if (sort) query = query.sort(sort);
      if (limit) query = query.limit(limit);
      result = await query.toArray();
    } else if (action === 'findOne') {
      result = await col.findOne(filter);
    } else if (action === 'update') {
      result = await col.updateOne(filter, { $set: data });
    } else if (action === 'delete') {
      result = await col.deleteOne(filter);
    } else {
      throw new Error('Unknown action: ' + action);
    }

    console.log(JSON.stringify({ success: true, data: result }));
    await mongoose.disconnect();
    process.exit(0);
  } catch (error) {
    console.log(JSON.stringify({ success: false, error: error.message }));
    try { await mongoose.disconnect(); } catch(e) {}
    process.exit(1);
  }
}

main();
