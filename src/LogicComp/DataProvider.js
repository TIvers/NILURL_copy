import React, { createContext, useContext, useState } from 'react';


const DataContext = createContext();


export const DataProvider = ({ children }) => {
  const [isPremium, setIsPremium] = useState("");

  return (
    <DataContext.Provider value={{ isPremium, setIsPremium }}>
      {children}
    </DataContext.Provider>
  );
};


export const usePremium = () => useContext(DataContext);