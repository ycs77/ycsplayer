declare module Resumable {
  export interface ConfigurationHash {
    /**
     * List of HTTP status codes that define if the chunk upload was a permanent error and should not retry the upload. (Default: `[400, 404, 409, 415, 500, 501]`)
     */
    permanentErrors?: number[]
  }
}
