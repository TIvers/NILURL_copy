import React, { useEffect, useRef } from "react";

export const useCustomRefHook = <T extends any>(dependencies: any[] | null): React.MutableRefObject<T | null> => {
  const ref = useRef<T | null>(null);

  useEffect(() => {
    
  }, Array.isArray(dependencies) ? dependencies : []); 

  return ref;
};
